<?php
/**
 * Copyright (C) 2018 yours! Ltd
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * Implements the page javascript for the jobs page.
 */

if(!function_exists('wordpatch_jobs_draw_page_js')) {
    /**
     * Draw the javascript for the jobs page.
     *
     * @param $wpenv_vars
     */
    function wordpatch_jobs_draw_page_js($wpenv_vars) {
        ?>
        <script type="text/javascript">
            (function ($) {
                $(document).ready(function () {
                    var jobsTableContainer = $('.wordpatch_jobs_container');
                    var jobsTable = jobsTableContainer.find('.wordpatch_jobs');

                    var initSortables = function () {
                        if(!jobsTable.length) {
                            return;
                        }

                        var container = jobsTable.eq(0)[0];
                        var sort = Sortable.create(container, {
                            animation: 150, // ms, animation speed moving items when sorting, `0` — without animation
                            handle: ".grip", // Restricts sort start click/touch to the specified element
                            draggable: ".wordpatch_job_ctr", // Specifies which items inside the element should be sortable
                            onUpdate: function (evt/**Event*/){
                                var newSortStr = "";
                                jobsTable.find(".wordpatch_job_ctr").each(function (index, val) {
                                    var row = $(this);
                                    if (newSortStr !== '') {
                                        newSortStr += ',';
                                    }
                                    newSortStr += row.attr('data-job-id');
                                });
                                $('#wordpatch_jobs_form :input[name="jobs_order"]').val(newSortStr);
                            }
                        });
                    };

                    initSortables();
                });
            })(jQuery);
        </script>
        <?php
    }
}