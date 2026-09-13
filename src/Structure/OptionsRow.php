<?php

namespace AllegedWizard\WPAdminOptions\Structure;

use AllegedWizard\WPAdminOptions\Bootstrap\WPAdminOptions;

/**
 * Lays several fields out side by side on one row of an OptionsContainer
 * (or any form-table). Each field keeps its normal label + input markup and
 * gets one column; the label sits above the input inside the column.
 *
 * Usage inside an OptionsContainer's `fields` callable:
 *
 *   new OptionsRow([
 *       'key'     => 'from',
 *       'columns' => 2,            // number of equal columns (default 2)
 *       'widths'  => ['2fr', '1fr'], // optional CSS grid track sizes instead
 *       'fields'  => function() {
 *           new InputOption([...]);
 *           new SelectOption([...]);
 *       },
 *   ]);
 */
class OptionsRow
{
    protected $args = [
        'key'     => '',
        'columns' => 2,
        'widths'  => [],
        'fields'  => null,
    ];

    public function __construct( $args = [] ) {
        $this->args = wp_parse_args( $args, $this->args );
        WPAdminOptions::init();
        $this->render();
    }

    public function render() {
        $key = esc_attr( $this->args['key'] );
        $fields = $this->args['fields'];

        // Each field renders a complete <tr>; split them apart so every
        // field can sit in its own single-row table inside a grid column.
        $fields_html = '';
        if ( is_callable( $fields ) ) {
            ob_start();
            call_user_func( $fields );
            $fields_html = ob_get_clean();
        }

        $rows = $this->split_rows( $fields_html );
        $columns = max( 1, (int) $this->args['columns'], count( $rows ) );
        $widths = array_values( array_filter( array_map( 'strval', (array) $this->args['widths'] ) ) );
        $template = ! empty( $widths )
            ? implode( ' ', array_map( 'esc_attr', $widths ) )
            : 'repeat(' . $columns . ', minmax(0, 1fr))';
        ?>
        <tr class="wao-row" <?= $key ? 'id="wao-row-' . $key . '"' : ''; ?>>
            <td colspan="2" class="wao-row-cell">
                <div class="wao-columns" style="grid-template-columns: <?= $template; ?>;">
                    <?php foreach ( $rows as $row ) : ?>
                        <div class="wao-column">
                            <table class="form-table wao-column-table"><?= $row; ?></table>
                        </div>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>
        <?php
    }

    /**
     * Split captured field markup into its top-level <tr> ... </tr> chunks.
     * Anything outside a row (scripts a field emits after its markup) is
     * appended to the preceding chunk so it still runs.
     *
     * @param string $html
     * @return string[]
     */
    protected function split_rows( $html ) {
        $chunks = [];
        $offset = 0;
        $length = strlen( $html );

        while ( $offset < $length ) {
            $start = stripos( $html, '<tr', $offset );
            if ( false === $start ) {
                break;
            }

            // Walk forward matching nested <tr> / </tr> pairs (Vue fields can
            // contain inner tables) until the opening tag is closed.
            $depth = 0;
            $pos = $start;
            $end = false;
            while ( false !== ( $next = $this->next_row_tag( $html, $pos, $is_close ) ) ) {
                $depth += $is_close ? -1 : 1;
                $pos = $next + 1;
                if ( 0 === $depth ) {
                    $end = strpos( $html, '>', $next ) + 1;
                    break;
                }
            }

            if ( false === $end ) {
                $chunks[] = substr( $html, $start );
                break;
            }

            $chunks[] = substr( $html, $start, $end - $start );
            $offset = $end;
        }

        // Trailing markup (inline scripts) belongs to the last field.
        if ( ! empty( $chunks ) && $offset < $length ) {
            $chunks[ count( $chunks ) - 1 ] .= substr( $html, $offset );
        }

        return $chunks;
    }

    /**
     * Position of the next <tr ...> or </tr> tag at or after $pos.
     *
     * @param string $html
     * @param int $pos
     * @param bool $is_close Set to whether the found tag is a closing tag.
     * @return int|false
     */
    protected function next_row_tag( $html, $pos, &$is_close ) {
        if ( ! preg_match( '/<(\/?)tr(?=[\s>])/i', $html, $m, PREG_OFFSET_CAPTURE, $pos ) ) {
            return false;
        }
        $is_close = '/' === $m[1][0];
        return (int) $m[0][1];
    }
}
