/**
 * Admin Meta Box JavaScript
 * Handles repeater field functionality for Ateso Words custom post type
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        /**
         * Add new repeater row
         */
        $(document).on('click', '.ateso-add-repeater-row', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $repeater = $button.closest('.ateso-repeater-field');
            var $rows = $repeater.find('.ateso-repeater-rows');
            var $template = $repeater.find('.ateso-repeater-template');

            if ($template.length === 0) {
                console.error('Repeater template not found');
                return;
            }

            // Get template HTML and replace placeholder index
            var templateHTML = $template.html();
            var currentIndex = $rows.find('.ateso-repeater-row').length;
            var newRowHTML = templateHTML.replace(/\{\{INDEX\}\}/g, currentIndex);

            // Add new row
            $rows.append(newRowHTML);

            // Focus on first field in new row
            $rows.find('.ateso-repeater-row:last textarea:first').focus();
        });

        /**
         * Remove repeater row
         */
        $(document).on('click', '.ateso-remove-repeater-row', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $row = $button.closest('.ateso-repeater-row');
            var $repeater = $button.closest('.ateso-repeater-field');
            var $rows = $repeater.find('.ateso-repeater-rows');

            // Check if there's more than one row
            if ($rows.find('.ateso-repeater-row').length <= 1) {
                // Clear the inputs instead of removing the last row
                $row.find('textarea').val('');
                return;
            }

            // Confirm before removing
            if (typeof atesoAdminMetabox !== 'undefined' && atesoAdminMetabox.confirmRemove) {
                if (!confirm(atesoAdminMetabox.confirmRemove)) {
                    return;
                }
            }

            // Remove row with fade animation
            $row.fadeOut(300, function() {
                $(this).remove();
                reindexRepeaterRows($repeater);
            });
        });

        /**
         * Reindex repeater rows after deletion
         * Ensures field names have sequential indices
         *
         * @param {jQuery} $repeater The repeater field container
         */
        function reindexRepeaterRows($repeater) {
            var fieldName = $repeater.data('field-name');
            var $rows = $repeater.find('.ateso-repeater-rows .ateso-repeater-row');

            $rows.each(function(index) {
                var $row = $(this);

                // Update data-index attribute
                $row.attr('data-index', index);

                // Update field names
                $row.find('textarea').each(function() {
                    var $textarea = $(this);
                    var name = $textarea.attr('name');

                    if (name) {
                        // Replace the index in field name
                        var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                        $textarea.attr('name', newName);
                    }
                });
            });
        }

        /**
         * Make meta boxes collapsible by default (except primary info)
         */
        function setupCollapsibleMetaBoxes() {
            // Close all meta boxes except primary info on page load
            $('.postbox').each(function() {
                var $metabox = $(this);
                var metaboxId = $metabox.attr('id');

                // Don't collapse the primary info meta box
                if (metaboxId === 'ateso_primary_info') {
                    return;
                }

                // Check if this meta box should be collapsed
                // WordPress stores collapsed state in user meta, so we respect that
                if (!$metabox.hasClass('closed')) {
                    // Only auto-collapse on first visit (if no saved state)
                    var postboxKey = 'ateso_metabox_' + metaboxId + '_closed';
                    var wasClosed = localStorage.getItem(postboxKey);

                    if (wasClosed === null) {
                        // First time - auto collapse non-primary boxes
                        $metabox.addClass('closed');
                        localStorage.setItem(postboxKey, 'true');
                    }
                }
            });

            // Track manual collapse/expand
            $('.postbox .hndle, .postbox .handlediv').on('click', function() {
                var $metabox = $(this).closest('.postbox');
                var metaboxId = $metabox.attr('id');
                var postboxKey = 'ateso_metabox_' + metaboxId + '_closed';

                // Toggle after a brief delay to catch the state change
                setTimeout(function() {
                    var isClosed = $metabox.hasClass('closed');
                    localStorage.setItem(postboxKey, isClosed ? 'true' : 'false');
                }, 100);
            });
        }

        /**
         * Auto-resize textareas as user types
         */
        function setupAutoResizeTextareas() {
            $(document).on('input', '.ateso-repeater-field textarea', function() {
                autoResizeTextarea(this);
            });

            // Initial resize for existing content
            $('.ateso-repeater-field textarea').each(function() {
                autoResizeTextarea(this);
            });
        }

        /**
         * Auto-resize a textarea based on content
         *
         * @param {HTMLElement} textarea The textarea element
         */
        function autoResizeTextarea(textarea) {
            var $textarea = $(textarea);
            var minRows = parseInt($textarea.attr('rows')) || 2;
            var lineHeight = parseInt($textarea.css('line-height'));
            var minHeight = minRows * lineHeight;

            // Reset height to recalculate
            $textarea.css('height', 'auto');

            // Set new height
            var newHeight = Math.max(minHeight, textarea.scrollHeight);
            $textarea.css('height', newHeight + 'px');
        }

        /**
         * Add sortable functionality to repeater rows (optional enhancement)
         */
        function setupSortableRepeaters() {
            if (typeof $.fn.sortable !== 'undefined') {
                $('.ateso-repeater-rows').sortable({
                    handle: '.ateso-repeater-row',
                    cursor: 'move',
                    opacity: 0.7,
                    placeholder: 'ateso-repeater-placeholder',
                    update: function(event, ui) {
                        var $repeater = $(this).closest('.ateso-repeater-field');
                        reindexRepeaterRows($repeater);
                    }
                });
            }
        }

        // Initialize on page load
        setupCollapsibleMetaBoxes();
        setupAutoResizeTextareas();
        // setupSortableRepeaters(); // Uncomment if you want drag-and-drop sorting

        /**
         * Form validation before publish
         */
        $('form#post').on('submit', function(e) {
            // Optional: Add validation logic here
            // Example: Ensure primary definition is filled
            var primaryDefinition = $('#primary_definition').val();

            if (!primaryDefinition || primaryDefinition.trim() === '') {
                // This is just a warning, not a blocker
                console.warn('Primary definition is empty');
            }

            return true; // Allow form submission
        });

    });

})(jQuery);
