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

    var tinymce = window.tinymce;
    var common = window.Claroline.Common;
    var home = window.Claroline.Home;
    var modal = window.Claroline.Modal;
    var resourceManager = window.Claroline.ResourceManager;
    var translator = window.Translator;
    var routing =  window.Routing;

    //Load external plugins
    tinymce.PluginManager.load('mention', home.asset + 'bundles/frontend/tinymce/plugins/mention/plugin.min.js');
    tinymce.PluginManager.load('accordion', home.asset + 'bundles/frontend/tinymce/plugins/accordion/plugin.min.js');
    tinymce.DOM.loadCSS(home.asset + 'bundles/frontend/tinymce/plugins/mention/css/autocomplete.css');

    /**
     * Claroline TinyMCE parameters and methods.
     */
    tinymce.claroline = {
        'disableBeforeUnload': false,
        'domChange': null
    };

    /**
     * This method fix the height of TinyMCE after modify it,
     * this is usefull when change manually something in the editor.
     *
     * @param editor A TinyMCE editor object.
     *
     */
    tinymce.claroline.editorChange = function (editor)
    {
        setTimeout(function () {
            var container = $(editor.getContainer()).find('iframe').first();
            var height = container.contents().height();
            var max = 'autoresize_max_height';
            var min = 'autoresize_min_height';

            switch (true)
            {
                case (height <= tinymce.claroline.configuration[min]):
                    container.css('height', tinymce.claroline.configuration[min]);
                    break;
                case (height >= tinymce.claroline.configuration[max]):
                    container.css('height', tinymce.claroline.configuration[max]);
                    container.css('overflow', 'scroll');
                    break;
                default:
                    container.css('height', height);
            }
        }, 500);
    };

    /**
     * This method if fired when paste in a TinyMCE editor.
     *
     *  @param plugin TinyMCE paste plugin object.
     *  @param args TinyMCE paste plugin arguments.
     *
     */
    tinymce.claroline.paste = function (plugin, args)
    {
        var link = $('<div>' + args.content + '</div>').text().trim(); //inside div because a bug of jquery

        home.canGenerateContent(link, function (data) {
            tinymce.activeEditor.insertContent('<div>' + data + '</div>');
            tinymce.claroline.editorChange(tinymce.activeEditor);
        });
    };

    /**
     * Check if a TinyMCE editor has change.
     *
     * @return boolean.
     *
     */
    tinymce.claroline.checkBeforeUnload = function ()
    {
        if (!tinymce.claroline.disableBeforeUnload) {
            for (var id in tinymce.editors) {
                if (tinymce.editors.hasOwnProperty(id) &&
                    tinymce.editors[id].isBeforeUnloadActive &&
                    tinymce.editors[id].getContent() !== '' &&
                    $(tinymce.editors[id].getElement()).data('saved')
                    ) {
                    return true;
                }
            }
        }

        return false;
    };

    /**
     * Set the edition detection parameter for a TinyMCE editor.
     *
     * @param editor A TinyMCE editor object.
     *
     */
    tinymce.claroline.setBeforeUnloadActive = function (editor)
    {
        if ($(editor.getElement()).data('before-unload') !== 'off') {
            editor.isBeforeUnloadActive = true;
        } else {
            editor.isBeforeUnloadActive = false;
        }
    };

    /**
     * Add or remove fullscreen class name in a modal containing a TinyMCE editor.
     *
     * @param editor A TinyMCE editor object.
     *
     */
    tinymce.claroline.toggleFullscreen = function (element)
    {
        $(element).parents('.modal').first().toggleClass('fullscreen');
    };

    /**
     * This method is fired when one or more resources are added to the editor
     * with a resource picker.
     *
     * @param nodes An array of resource nodes.
     *
     */
    tinymce.claroline.callBack = function (nodes)
    {
        var nodeId = _.keys(nodes)[0];
        var mimeType = nodes[_.keys(nodes)][2] !== '' ? nodes[_.keys(nodes)][2] : 'unknown/mimetype';
        var openInNewTab = tinymce.activeEditor.getParam('picker').openResourcesInNewTab ? '1' : '0';

        $.ajax(home.path + 'resource/embed/' + nodeId + '/' + mimeType + '/' + openInNewTab)
            .done(function (data) {
                tinymce.activeEditor.insertContent(data);
                if (!tinymce.activeEditor.plugins.fullscreen.isFullscreen()) {
                    tinymce.claroline.editorChange(tinymce.activeEditor);
                }
            })
            .error(function () {
                modal.error();
            });
    };

    /**
     * Open a resource picker from a TinyMCE editor.
     */
    tinymce.claroline.resourcePickerOpen = function ()
    {
        if (!resourceManager.hasPicker('tinyMcePicker')) {
            resourceManager.createPicker('tinyMcePicker', {
                callback: tinymce.claroline.callBack
            }, true);
        } else {
            resourceManager.picker('tinyMcePicker', 'open');
        }
    };

    /**
     * Open a resource picker from a TinyMCE editor.
     */
    tinymce.claroline.directoryPickerOpen = function ()
    {
        if (!resourceManager.hasPicker('tinyMceDirectoryPicker')) {
            resourceManager.createPicker('tinyMceDirectoryPicker', {
                callback: tinymce.claroline.directoryPickerCallBack,
                resourceTypes: ['directory'],
                isDirectorySelectionAllowed: true,
                isPickerMultiSelectAllowed: false
            }, true);
        } else {
            resourceManager.picker('tinyMceDirectoryPicker', 'open');
        }
    };

    /**
     * Open a directory picker from a TinyMCE editor.
     */
    tinymce.claroline.directoryPickerCallBack = function(nodes)
    {
        for (var id in nodes) {
            var val = nodes[id][4];
            var path = nodes[id][3];
        }

        //file_form_destination
        var html = '<option value="' + val + '">' + path + '</option>';
        $('#file_form_destination').append(html);
        $('#file_form_destination').val(val);
    }

    /**
     * Add resource picker and upload files buttons in a TinyMCE editor if data-resource-picker is on.
     *
     * @param editor A TinyMCE editor object.
     *
     */
    tinymce.claroline.addResourcePicker = function (editor)
    {
        if ($(editor.getElement()).data('resource-picker') !== 'off') {
            editor.addButton('resourcePicker', {
                'icon': 'none fa fa-folder-open',
                'classes': 'widget btn',
                'tooltip': translator.trans('resources', {}, 'platform'),
                'onclick': function () {
                    tinymce.activeEditor = editor;
                    tinymce.claroline.resourcePickerOpen();
                }
            });
            editor.addButton('fileUpload', {
                'icon': 'none fa fa-file',
                'classes': 'widget btn',
                'tooltip': translator.trans('upload', {}, 'platform'),
                'onclick': function () {
                    tinymce.activeEditor = editor;
                    modal.fromRoute('claro_upload_modal', null, function (element) {
                        element.on('click', '.resourcePicker', function () {
                            tinymce.claroline.resourcePickerOpen();
                        })
                        .on('click', '.filePicker', function () {
                            $('#file_form_file').click();
                        })
                        .on('change', '#file_form_destination', function(event) {
                            if ($('#file_form_destination').val() === 'others') {
                                tinymce.claroline.directoryPickerOpen();
                            }
                        })
                        .on('change', '#file_form_file', function () {
                            common.uploadfile(
                                this,
                                element,
                                $('#file_form_destination').val(),
                                tinymce.claroline.callBack
                            );
                        })
                    });
                }
            });
        }
    };

    /**
     * Setup configuration of TinyMCE editor.
     *
     * @param editor A TinyMCE editor object.
     *
     */
    tinymce.claroline.setup = function (editor)
    {
        editor.on('change', function () {
            if (editor.getElement()) {
                editor.getElement().value = editor.getContent();
                if (editor.isBeforeUnloadActive) {
                    $(editor.getElement()).data('saved', 'false');
                    tinymce.claroline.disableBeforeUnload = false;
                }
            }
        }).on('LoadContent', function () {
            tinymce.claroline.editorChange(editor);
        });

        editor.on('BeforeRenderUI', function () {
            editor.theme.panel.find('toolbar').slice(1).hide();
        });

        // Add a button that toggles toolbar 1+ on/off
        editor.addButton('displayAllButtons', {
            'icon': 'none fa fa-chevron-down',
            'classes': 'widget btn',
            'tooltip': translator.trans('tinymce_all_buttons', {}, 'platform'),
            onclick: function () {
                if (!this.active()) {
                    this.active(true);
                    editor.theme.panel.find('toolbar').slice(1).show();
                } else {
                    this.active(false);
                    editor.theme.panel.find('toolbar').slice(1).hide();
                }
            }
        });

        tinymce.claroline.addResourcePicker(editor);
        tinymce.claroline.setBeforeUnloadActive(editor);
        $('body').bind('ajaxComplete', function () {
            setTimeout(function () {
                if (editor.getElement() && editor.getElement().value === '') {
                    editor.setContent('');
                }
            }, 200);
        });
    };

    /**
     * @todo documentation
     */
    tinymce.claroline.mentionsSource = function (query, process, delimiter)
    {
        if (!_.isUndefined(window.Workspace) && !_.isNull(window.Workspace.id)) {
            if (delimiter === '@' && query.length > 0) {
                var searchUserInWorkspaceUrl = routing.generate('claro_user_search_in_workspace') + '/';

                $.getJSON(searchUserInWorkspaceUrl + window.Workspace.id + '/' + query, function (data) {
                    if (!_.isEmpty(data) && !_.isUndefined(data.users) && !_.isEmpty(data.users)) {
                        process(data.users);
                    }
                });
            }
        }
    };

    /**
     * @todo documentation
     */
    tinymce.claroline.mentionsItem = function (item)
    {
        var avatar = '<i class="fa fa-user"></i>';
        if (item.avatar !== null) {
            avatar = '<img src="' + home.asset + 'uploads/pictures/' + item.avatar + '" alt="' + item.name +
                     '" class="img-responsive">';
        }

        return '<li>' +
            '<a href="javascript:;"><span class="user-picker-dropdown-avatar">' + avatar + '</span>' +
            '<span class="user-picker-dropdown-name">' + item.name + '</span>' +
            '<small class="user-picker-avatar-mail text-muted">(' + item.mail + ')</small></a>' +
            '</li>';
    };

    /**
     * @todo documentation
     */
    tinymce.claroline.mentionsInsert = function (item)
    {
        var publicProfileUrl = routing.generate('claro_public_profile_view') + '/';

        return '<user id="' + item.id + '"><a href="' + publicProfileUrl + item.id + '">' + item.name + '</a></user>';
    };

    /**
     * Configuration and parameters of a TinyMCE editor.
     */
    tinymce.claroline.configuration = {
        'relative_urls': false,
        'theme': 'modern',
        'language': home.locale.trim(),
        'browser_spellcheck': true,
        'autoresize_min_height': 100,
        'autoresize_max_height': 500,
        'content_css': home.asset + 'css/clarolinecore/tinymce.css',
        'plugins': [
            'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars fullscreen',
            'insertdatetime media nonbreaking save table directionality',
            'template paste textcolor emoticons code -mention -accordion'
        ],
        'toolbar1': 'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | ' +
                    'resourcePicker fileUpload | fullscreen displayAllButtons',
        'toolbar2': 'styleselect | undo redo | forecolor backcolor | bullist numlist | outdent indent | ' +
                    'image media link charmap | print preview code',
        'extended_valid_elements': 'user[id], a[data-toggle|data-parent]',
        'paste_preprocess': tinymce.claroline.paste,
        'setup': tinymce.claroline.setup,
        'mentions': {
            'source': tinymce.claroline.mentionsSource,
            'render': tinymce.claroline.mentionsRender,
            'insert': tinymce.claroline.mentionsInsert,
            'delay': 200
        },
        'picker': {
            'openResourcesInNewTab': false
        }
    };

    /**
     * Initialization function for TinyMCE editors.
     */
    tinymce.claroline.initialization = function ()
    {
        $('textarea.claroline-tiny-mce:not(.tiny-mce-done)').each(function () {
            var element = $(this);
            var config = null;

            if (element.data('newTab') === 'yes') {
                config = _.extend({}, tinymce.claroline.configuration);
                config.picker.openResourcesInNewTab = true;
            } else {
                config = tinymce.claroline.configuration;
            }

            element.tinymce(config)
                .on('remove', function () {
                    var editor = tinymce.get(element.attr('id'));
                    if (editor) {
                        editor.destroy();
                    }
                })
                .addClass('tiny-mce-done');
        });
    };

    /** Events **/

    $('body').bind('ajaxComplete', function () {
        tinymce.claroline.initialization();
    })
    .on('click', '.mce-widget.mce-btn[aria-label="Fullscreen"]', function () {
        tinymce.claroline.toggleFullscreen(this);
        $(window).scrollTop($(this).parents('.mce-tinymce.mce-container.mce-panel').first().offset().top);
        window.dispatchEvent(new window.Event('resize'));
    })
    .bind('DOMSubtreeModified', function () {
        clearTimeout(tinymce.claroline.domChange);
        tinymce.claroline.domChange = setTimeout(tinymce.claroline.initialization, 10);
    })
    .on('click', 'form *[type=submit]', function () {
        tinymce.claroline.disableBeforeUnload = true;
    });

    $(document).ready(function () {
        tinymce.claroline.initialization();
    });

    $(window).on('beforeunload', function () {
        if (tinymce.claroline.checkBeforeUnload()) {
            return translator.trans('leave_this_page', {}, 'platform');
        }
    });
}());
