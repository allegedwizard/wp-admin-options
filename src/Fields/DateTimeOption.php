<?php

namespace AllegedWizard\WPAdminOptions\Fields;

class DateTimeOption extends AbstractAdminOption
{
    public function render_admin_table() {
        if ( ! empty( $this->args['multiple'] ) ) {
            if ( $this->render_array_error() ) return;
            $this->render_multiple();
        } else {
            $this->render_single();
        }
    }

    public function render_single() {
        $key = esc_attr( $this->args['key'] );
        ?>
        <tr id="<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td>
                <div class="wao-datetime">
                    <input type="date" v-model="date" required>
                    <input type="time" v-model="time" required>
                </div>
                <input type="hidden" name="<?= $key; ?>" :value="json">
            </td>
        </tr>
        <?php
        add_action( 'admin_footer', [$this, 'render_script'] );
    }

    public function render_multiple() {
        $key = esc_attr( $this->args['key'] );
        $description = trim( $this->args['description'] );
        ?>
        <tr id="row-<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td id="<?= $key; ?>-wrap" class="wao-vue-wrap">
                <div class="wao-action-group">
                    <button type="button" class="button" @click="addItem()">Add Date/Time</button>
                </div>
                <hr>
                <div class="wao-items">
                    <p v-if="!items.length">
                        No date/time entries.
                    </p>
                    <div v-for="(item, i) in items" class="wao-item" :key="item._uid"
                         :class="{'wao-dragging': dragIndex === i, 'wao-dragover': dragOverIndex === i}"
                         draggable="true"
                         @dragstart="dragStart(i, $event)"
                         @dragover.prevent="dragOver(i)"
                         @drop="drop(i)"
                         @dragend="dragEnd">
                        <div class="wao-item-row">
                            <span class="wao-drag-handle" title="Drag to reorder">&#x2630;</span>
                            <div class="wao-datetime">
                                <input type="date" v-model="item.date" required>
                                <input type="time" v-model="item.time" required>
                            </div>
                            <div class="wao-controls">
                                <button type="button" class="button" :disabled="!canMoveUp(i)" @click="moveUp(i)">&#x25B2;</button>
                                <button type="button" class="button" :disabled="!canMoveDown(i)" @click="moveDown(i)">&#x25BC;</button>
                                <button type="button" class="button" @click="removeItem(i)">&times;</button>
                            </div>
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

        if ( ! empty( $this->args['multiple'] ) ) {
            $items = [];
            foreach ( (array) $this->args['value'] as $val ) {
                $val = trim( (string) $val );
                if ( '' === $val ) {
                    $items[] = [ 'date' => '', 'time' => '', '_uid' => uniqid( 'dt_', true ) ];
                    continue;
                }
                $ts = strtotime( $val );
                $items[] = [
                    'date' => $ts ? date( 'Y-m-d', $ts ) : '',
                    'time' => $ts ? date( 'H:i:s', $ts ) : '',
                    '_uid' => uniqid( 'dt_', true ),
                ];
            }
            $args = [ 'key' => $key, 'mode' => 'multiple', 'items' => $items ];
        } else {
            $value = trim( (string) $this->args['value'] );
            $date = '';
            $time = '';
            if ( '' !== $value ) {
                $ts = strtotime( $value );
                if ( $ts ) {
                    $date = date( 'Y-m-d', $ts );
                    $time = date( 'H:i:s', $ts );
                }
            }
            $args = [ 'key' => $key, 'mode' => 'single', 'date' => $date, 'time' => $time ];
        }
        ?>
        <script>window.addEventListener('load', function() {
            WPAdminOptions.DateTimeOption(<?= json_encode( $args ); ?>);
        });</script>
        <?php
    }

}
