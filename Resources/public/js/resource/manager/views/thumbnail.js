/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global Twig */
/* global ResourceManagerThumbnail */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.ResourceManager = window.Claroline.ResourceManager || {};
    window.Claroline.ResourceManager.Views = window.Claroline.ResourceManager.Views || {};

    Claroline.ResourceManager.Views.Thumbnail = Backbone.View.extend({
        className: 'node-thumbnail node ui-state-default',
        tagName: 'li',
        events: {
            'click a.node-menu-action': 'menuAction'
        },
        initialize: function (parameters, dispatcher, zoomValue) {
            this.parameters = parameters;
            this.dispatcher = dispatcher;
            this.zoomValue = zoomValue;
        },
        menuAction: function (event) {
            event.preventDefault();
            var action = event.currentTarget.getAttribute('data-action');
            var nodeId = event.currentTarget.getAttribute('data-id');
            var isCustom = event.currentTarget.getAttribute('data-is-custom');
            var eventName = isCustom === 'no' ? action : 'custom-action';
            this.dispatcher.trigger(eventName, {
                action: action,
                nodeId: nodeId,
                view: this.parameters.viewName
            });
        },
        render: function (node, isSelectionAllowed) {
            this.el.id = node.id;
            this.$el.addClass(this.zoomValue);
            node.displayableName = Claroline.Utilities.formatText(node.name, 20, 2);
            isSelectionAllowed = (node.type === 'directory' && !this.parameters.isDirectorySelectionAllowed) ? false: true;
            var actions = this.parameters.resourceTypes.hasOwnProperty(node.type) ?
                this.parameters.resourceTypes[node.type].actions :
                [];
            this.el.innerHTML = Twig.render(ResourceManagerThumbnail, {
                'node': node,
                'isSelectionAllowed': isSelectionAllowed,
                'hasMenu': true,
                'actions': actions,
                'webRoot': this.parameters.webPath,
            });
        }
    });
})();
