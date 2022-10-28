/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    var calendar = window.Claroline.Calendar = {};

    function t(key) {
        return Translator.trans(key, {}, 'agenda');
    }

    calendar.initialize = function (
        context,
        workspaceId,
        canCreate
    ) {
        context = context || 'desktop';
        workspaceId = workspaceId || null;
        //the creation is enabled by default
        if (canCreate === undefined) {
            calendar.canCreate = true;
        } else {
            calendar.canCreate = canCreate;
        }
        calendar.flashbag =
            '<div class="alert alert-success">' +
                '<a data-dismiss="alert" class="close" href="#" aria-hidden="true">&times;</a>' +
                Translator.trans('edit_event_success', {}, 'platform') +
            '</div>';

        //initialize route & url depending on the context
        if (context !== 'desktop') {
            calendar.addUrl = Routing.generate('claro_workspace_agenda_add_event_form', {'workspace': workspaceId});
            calendar.showUrl = Routing.generate('claro_workspace_agenda_show', {'workspace': workspaceId});
        } else {
            calendar.addUrl = Routing.generate('claro_desktop_agenda_add_event_form');
            calendar.showUrl = Routing.generate('claro_desktop_agenda_show');
        }

        $('#import-ics-btn').on('click', function (event) {
            event.preventDefault();
            window.Claroline.Modal.displayForm(
                $(event.target).attr('href'),
                addItemsToCalendar,
                function () {},
                'ics-import-form'
            );
        });

        $('body').on('click', '.delete-event', function (event) {
            event.preventDefault();
            window.Claroline.Modal.confirmRequest(
                $(event.currentTarget).attr('href'),
                removeEvent,
                undefined,
                Translator.trans('remove_event_confirm', {}, 'platform'),
                Translator.trans('remove_event', {}, 'platform')
            );
        });

        //popover edit button: trigger the edit form
        $('body').on('click', '.edit-event-link', function(event) {
            event.preventDefault();
            window.Claroline.Modal.displayForm(
                $(event.currentTarget).attr('href'),
                updateCalendarItemCallback,
                function () {$('#agenda_form_isTask').is(':checked') ? hideFormDates(): showFormDates();},
                'form-event'
            );
        });

        $('.filter').click(function () {
            var workspaceIds = [];

            $('.filter:checkbox:checked').each(function () {
                workspaceIds.push(parseInt($(this).val()));
            });

            filterCalendarItems(workspaceIds);
        });

        //hide the dates if it's a task.
        $('body').on('click', '#agenda_form_isTask', function() {
            $('#agenda_form_isTask').is(':checked') ? hideFormDates(): showFormDates();
        });

        //INITIALIZE CALENDAR
        $('#calendar').fullCalendar({
            header: {
                left: 'prev, next today',
                center: 'title',
                right: 'month, agendaWeek, agendaDay'
            },
            columnFormat: {
                month: 'ddd',
                week: 'ddd d/M',
                day: 'dddd d/M'
            },
            buttonText: {
                prev: t('prev'),
                next: t('next'),
                prevYear: t('prevYear'),
                nextYear: t('nextYear'),
                today: t('today'),
                month: t('month'),
                week: t('week'),
                day: t('day')
            },
            firstDay: 1,
            monthNames: [t('january'), t('february'), t('march'), t('april'), t('may'), t('june'), t('july'),
                t('august'), t('september'), t('october'), t('november'), t('december')],
            monthNamesShort: [t('jan'), t('feb'), t('mar'), t('apr'), t('may'), t('ju'), t('jul'),
                t('aug'), t('sept'),  t('oct'), t('nov'), t('dec')],
            dayNames: [ t('sunday'),t('monday'), t('tuesday'), t('wednesday'), t('thursday'), t('friday'), t('saturday')],
            dayNamesShort: [ t('sun'), t('mon'), t('tue'), t('wed'), t('thu'), t('fri'), t('sat')],
            editable: true,
            //This is the url wich will get the events from ajax the 1st time the calendar is launched
            events: calendar.showUrl,
            axisFormat: 'HH:mm',
            timeFormat: 'H:mm',
            agenda: 'h:mm{ - h:mm}',
            '': 'h:mm{ - h:mm}',
            allDaySlot: false,
            lazyFetching : false,
            eventDrop: function (event, delta, revertFunc, jsEvent, ui, view) {
                move(event, delta._days, delta._milliseconds / (1000 * 60));
            },
            dayClick: renderAddEventForm,
            eventClick:  function (event, jsEvent, view) {
                //don't do anything because it's the "edit" button from the popover that is going to trigger the modal
            },
            //renders the popover for an event
            eventRender: function (event, element) {
                //event are unfiltered by default
                event.visible = event.visible === undefined ? true: event.visible;
                if (!event.visible) return false;
                renderEvent(event, element);
            },
            eventResize: function (event, delta, revertFunc, jsEvent, ui, view) {
                resize(event, delta._days, delta._milliseconds / (1000 * 60));
            }
        });
    };

    var hidePopovers = function () {
        $('.fc-event').popover('hide');
    }

    var rerenderEvent = function(event, element) {
        //destroy old popover
        element.popover('destroy');
        renderEvent(event, element);
    }

    //@todo move this on the eventClick event ?
    var renderEvent = function (event, element) {
        var eventContent = Twig.render(EventContent, {'event': event});
        element.popover({
            title: event.title + '<button type="button" class="pop-close close" data-dismiss="popover" aria-hidden="true">&times;</button>',
            content: eventContent,
            html: true,
            container: 'body'
        });
    }

    var renderAddEventForm = function (date) {
        if (calendar.canCreate) {
            var dateVal = moment(date).format(Translator.trans('date_agenda_display_format', {}, 'platform'));

            var postRenderAddEventAction = function (html) {
                $('#agenda_form_start').val(dateVal);
                $('#agenda_form_end').val(dateVal);
            };

            window.Claroline.Modal.displayForm(
                calendar.addUrl,
                addItemsToCalendar,
                postRenderAddEventAction,
                'form-event'
            );
        }
    };

    var addEventToCalendar = function (event) {
        $('#calendar').fullCalendar(
            'renderEvent',
            {
                id: event.id,
                title: event.title,
                start: event.start,
                end: event.end,
                allDay: event.allDay,
                color: event.color,
                description : event.description,
                deletable: event.deletable,
                editable: event.editable,
                endFormatted: event.endFormatted,
                startFormatted: event.startFormatted,
                owner: event.owner
            }
        );
    };

    var addTaskToCalendar = function (event) {
        var html = Twig.render(Task, {'event': event});
        $('#tasks-list').append(html);
    }

    var addItemsToCalendar = function (events) {
        for (var i = 0; i < events.length; i++) {
            events[i].allDay ? addTaskToCalendar(events[i]):  addEventToCalendar(events[i]);
        }
    }

    var updateCalendarItem = function (event) {
        removeEvent(undefined, undefined, event);
        addItemsToCalendar(new Array(event));
    }

    var updateCalendarItemCallback = function (event) {
        hidePopovers();
        updateCalendarItem(event);
        $('.panel-body').first().prepend(calendar.flashbag);
    }

    var removeEvent = function (event, item, data) {
        hidePopovers();
        //Remove from the calendar if it exists.
        $('#calendar').fullCalendar('removeEvents', data.id);
        //Remove from the task bar if it exists.
        $('#li-task-' + data.id).hide();
    }

    /**
     * If action = 'move': the event will be moved
     * If action = 'resize': the event will be resized
     *
     * @param event
     * @param dayDelta
     * @param minuteDelta
     * @param action
     */
    var resizeOrMove = function (event, dayDelta, minuteDelta, action) {
        var route = action === 'move' ? 'claro_workspace_agenda_move': 'claro_workspace_agenda_resize';

        $.ajax({
            'url': Routing.generate(route, {'event': event.id, 'day': dayDelta, 'minute': minuteDelta}),
            'type': 'POST',
            'success': function (event) {
                $('.panel-body').first().prepend(calendar.flashbag);
                rerenderEvent(event, $('.' + event.className));
            },
            'error': function () {
                //do more error handling here
                alert('error');
                updateCalendarItem(event);
            }
        });
    }

    var move = function (event, dayDelta, minuteDelta) {
        resizeOrMove(event, dayDelta, minuteDelta, 'move');
    }

    var resize = function (event, dayDelta, minuteDelta) {
        resizeOrMove(event, dayDelta, minuteDelta, 'resize');
    }

    var filterEvents = function (workspaceIds) {
        var numberOfChecked = $('.filter:checkbox:checked').length;
        var totalCheckboxes = $('.filter:checkbox').length;
        //if all checkboxes or none checkboxes are checked display all events
        if ((totalCheckboxes - numberOfChecked === 0) || (numberOfChecked === 0)) {
            $('#calendar').fullCalendar('clientEvents', function (eventObject) {
                eventObject.visible = true;
            });
        } else {
            for (var i = 0; i < workspaceIds.length; i++) {
                $('#calendar').fullCalendar('clientEvents', function (eventObject) {
                    //check for workspace
                    eventObject.visible = ($.inArray(eventObject.workspace_id, workspaceIds) >= 0) ? true: false;
                    //check for desktop
                    if (($.inArray(0, workspaceIds) >= 0) && eventObject.workspace_id === null) {
                        eventObject.visible = true;
                    }
                });
            }
        }
        $('#calendar').fullCalendar('rerenderEvents');
    }

    var filterTasks = function (workspaceIds) {
        var numberOfChecked = $('.filter:checkbox:checked').length;
        var totalCheckboxes = $('.filter:checkbox').length;

        if ((totalCheckboxes - numberOfChecked === 0) || (numberOfChecked === 0)) {
            $('.task-item').each(function () {
                $(this).show();
            });
        } else {
            //hide what's needed
            $('.task-item').each(function() {
                if ($.inArray(parseInt($(this).attr('data-workspace-id')), workspaceIds) < 0) $(this).hide();
            });
        }
    }

    var hideFormDates = function() {
        $('#agenda_form_end').parent().parent().hide();
        $('#agenda_form_endHours').parent().parent().hide();
        $('#agenda_form_start').parent().parent().hide();
        $('#agenda_form_startHours').parent().parent().hide();
    }

    var showFormDates = function() {
        $('#agenda_form_end').parent().parent().show();
        $('#agenda_form_endHours').parent().parent().show();
        $('#agenda_form_start').parent().parent().show();
        $('#agenda_form_startHours').parent().parent().show();
    }

    /**
     * Filter by workspace ids.
     * The id "0" is an exception for the desktop
     * @param selected
     */
    var filterCalendarItems = function (workspaceIds) {
        filterEvents(workspaceIds);
        filterTasks(workspaceIds);
    }
}) ();
