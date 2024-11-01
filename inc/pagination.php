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
 * Implements the shared pagination functionality.
 */

if(!function_exists('wordpatch_pagination')) {
    /**
     * Draws generic pagination links for WordPatch.
     *
     * @param $wpenv_vars
     * @param $page
     * @param $total_pages
     * @param $where
     */
    function wordpatch_pagination($wpenv_vars, $page, $total_pages, $where)
    {
        // Construct the name of the builder functions
        $prev_builder_fn = "wordpatch_{$where}_pagination_prev_builder";
        $next_builder_fn = "wordpatch_{$where}_pagination_next_builder";
        $builder_fn = "wordpatch_{$where}_pagination_builder";

        $peek_count = 3;

        // We want to draw three leading, current, and three trailing. Then we want to draw range dots to connect to
        // first three and last three pages.
        // ie: <  1 ... 40  41  42  [43]  44  45  46 ... 100  >
        // ie: <  1  2  3  4  [5]  6  7  8 ... 100 >

        $show_prev_button = $page > 1;
        $show_next_button = $page < $total_pages;

        $show_pre_ellipses = $page > (2 + $peek_count);
        $show_end_ellipses = ($page + $peek_count + 1) < $total_pages;

        // Draw the previous button
        if($show_prev_button) {
            $prev_page = $page - 1;

            $prev_html = call_user_func_array($prev_builder_fn, array($wpenv_vars, $prev_page));
            echo($prev_html);
        }

        // Draw the first page
        if($total_pages > 0) {
            $link_html = call_user_func_array($builder_fn, array($wpenv_vars, 1, $page === 1));
            echo($link_html);
        }

        // Draw the ellipses maybe
        if($show_pre_ellipses) {
            ?>
            <span class="wordpatch_pagination_ellipses wordpatch_pagination_ellipses_pre"><?php echo(__wte($wpenv_vars,
                    'MORE_PAGES')); ?></span>
            <?php
        }

        // Draw the current range
        $start_page = max(1, $page - $peek_count);
        $end_page = min($total_pages, $page + $peek_count);

        for($range_current = $start_page; $range_current <= $end_page; $range_current++) {
            // Do not draw the first or last page
            if($range_current === 1 || $range_current === $total_pages) {
                continue;
            }

            $link_html = call_user_func_array($builder_fn, array($wpenv_vars, $range_current, $range_current === $page));
            echo($link_html);
        }

        // Draw the ellipses maybe
        if($show_end_ellipses) {
            ?>
            <span class="wordpatch_pagination_ellipses wordpatch_pagination_ellipses_end"><?php echo(__wte($wpenv_vars,
                    'MORE_PAGES')); ?></span>
            <?php
        }

        // Draw the last page
        if($total_pages > 1) {
            $link_html = call_user_func_array($builder_fn, array($wpenv_vars, $total_pages, $page === $total_pages));
            echo($link_html);
        }

        // Draw the next button
        if($show_next_button) {
            $next_page = $page + 1;

            $next_html = call_user_func_array($next_builder_fn, array($wpenv_vars, $next_page));
            echo($next_html);
        }
    }
}