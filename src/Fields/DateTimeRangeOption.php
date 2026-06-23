<?php

namespace AllegedWizard\WPAdminOptions\Fields;

class DateTimeRangeOption extends AbstractAdminOption
{
    public function render_admin_table() {
        if ( $this->render_array_error() ) return;
        $key = esc_attr( $this->args['key'] );
        $multiple = ! empty( $this->args['multiple'] );
        $description = trim( $this->args['description'] );
        $step = $this->time_step_attr();
        ?>
        <tr id="row-<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td id="<?= $key; ?>-wrap" class="wao-vue-wrap">
                <?php if ( $multiple ) : ?>
                <div class="wao-action-group">
                    <button type="button" class="button" @click="addItem()">Add Range</button>
                </div>
                <hr>
                <?php endif; ?>
                <div class="wao-items">
                    <?php if ( $multiple ) : ?>
                    <p v-if="!items.length">
                        No date ranges.
                    </p>
                    <?php endif; ?>
                    <div v-for="(item, i) in items" class="wao-item" :key="item._uid"
                         <?php if ( $multiple ) : ?>
                         :class="{'wao-dragging': dragIndex === i, 'wao-dragover': dragOverIndex === i}"
                         draggable="true"
                         @dragstart="dragStart(i, $event)"
                         @dragover.prevent="dragOver(i)"
                         @drop="drop(i)"
                         @dragend="dragEnd"
                         <?php endif; ?>>
                        <div class="wao-item-row">
                            <?php if ( $multiple ) : ?>
                            <span class="wao-drag-handle" title="Drag to reorder">&#x2630;</span>
                            <?php endif; ?>
                            <div class="wao-datetime-range">
                                <div class="wao-datetime-range-field">
                                    <span class="wao-field-label">Start</span>
                                    <div class="wao-datetime">
                                        <input type="date" v-model="item.start_date" required>
                                        <input type="time" v-model="item.start_time"<?= $step; ?> required>
                                    </div>
                                </div>
                                <div class="wao-datetime-range-field">
                                    <span class="wao-field-label">End</span>
                                    <div class="wao-datetime">
                                        <input type="date" v-model="item.end_date" required>
                                        <input type="time" v-model="item.end_time"<?= $step; ?> required>
                                    </div>
                                </div>
                            </div>
                            <?php if ( $multiple ) : ?>
                            <div class="wao-controls">
                                <button type="button" class="button" :disabled="!canMoveUp(i)" @click="moveUp(i)">&#x25B2;</button>
                                <button type="button" class="button" :disabled="!canMoveDown(i)" @click="moveDown(i)">&#x25BC;</button>
                                <button type="button" class="button" @click="removeItem(i)">&times;</button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
                if ( !empty( $description ) ) {
                    printf( '<p><code>%s</code></p>', $description );
                }
                ?>
                <input type="hidden" name="<?= $key; ?>" :value="json">
            </td>
        </tr>
        <?php
        add_action( 'admin_footer', [$this, 'render_script'] );
    }

    public function render_script() {
        $key = esc_attr( $this->args['key'] );
        $multiple = ! empty( $this->args['multiple'] );
        $fmt = $this->time_format();
        $items = [];

        foreach ( (array) $this->args['value'] as $pair ) {
            if ( ! is_array( $pair ) ) continue;
            $start = isset( $pair[0] ) ? trim( (string) $pair[0] ) : '';
            $end   = isset( $pair[1] ) ? trim( (string) $pair[1] ) : '';
            $start_ts = $start ? strtotime( $start ) : false;
            $end_ts   = $end   ? strtotime( $end )   : false;
            $items[] = [
                'start_date' => $start_ts ? date( 'Y-m-d', $start_ts ) : '',
                'start_time' => $start_ts ? date( $fmt, $start_ts ) : '',
                'end_date'   => $end_ts   ? date( 'Y-m-d', $end_ts )   : '',
                'end_time'   => $end_ts   ? date( $fmt, $end_ts )   : '',
                '_uid'       => uniqid( 'dtr_', true ),
            ];
        }

        if ( ! $multiple ) {
            // Single mode: always render exactly one row.
            if ( empty( $items ) ) {
                $items[] = [
                    'start_date' => '', 'start_time' => '',
                    'end_date'   => '', 'end_time'   => '',
                    '_uid'       => uniqid( 'dtr_', true ),
                ];
            } elseif ( count( $items ) > 1 ) {
                $items = array_slice( $items, 0, 1 );
            }
        }

        $args = [
            'key'   => $key,
            'mode'  => $multiple ? 'multiple' : 'single',
            'items' => $items,
        ];
        ?>
        <script>window.addEventListener('load', function() {
            WPAdminOptions.DateTimeRangeOption(<?= json_encode( $args ); ?>);
        });</script>
        <?php
    }

    /**
     * Whether the time inputs accept a seconds component. Off by default
     * (HH:MM); pass `'seconds' => true` to the option to allow HH:MM:SS.
     */
    protected function allow_seconds(): bool {
        return ! empty( $this->args['seconds'] );
    }

    /**
     * `step` attribute fragment for the <input type="time">. `step="1"` enables
     * the seconds component; omitting step leaves the browser default (minutes).
     * A value carrying seconds with no step is rejected as "not a valid value".
     */
    protected function time_step_attr(): string {
        return $this->allow_seconds() ? ' step="1"' : '';
    }

    /** date() format for the time value, matching the input's granularity. */
    protected function time_format(): string {
        return $this->allow_seconds() ? 'H:i:s' : 'H:i';
    }
}
