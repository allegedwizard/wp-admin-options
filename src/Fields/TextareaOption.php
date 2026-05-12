<?php
namespace AllegedWizard\WPAdminOptions\Fields;

class TextareaOption extends AbstractAdminOption
{
    protected function render_textarea_content() {
        $readonly = $this->args['readonly'];
        $key = esc_attr( $this->args['key'] );
        $value = esc_textarea( $this->args['value'] );
        $css_classes = esc_attr( trim( implode( ' ', $this->args['css_classes'] ) ) );
        $rows = esc_attr( $this->args['rows'] );
        ?>
        <textarea
            id="<?= $key; ?>"
            name="<?= $key; ?>"
            rows="<?= $rows; ?>"
            value="<?= $value; ?>"
            class="<?= $css_classes; ?>"
            <?php if ( $readonly ) : ?>readonly="readonly"<?php endif; ?>
            ><?= $value; ?></textarea>
        <?php
    }

    public function render_taxonomy_field() {
        $key = esc_attr( $this->args['key'] );
        $description = trim( $this->args['description'] );
        $show_color = $this->should_render_with_color();
        ?>
        <div class="form-field" id="row-<?= $key; ?>">
            <?php $this->render_option_label( false ); ?>
            <?php if ( $show_color ) : ?>
            <div class="wao-copy-wrap wao-input-has-color wao-textarea-color-wrap">
                <?php $this->render_textarea_content(); ?>
                <?php $this->render_color_picker_inline(); ?>
            </div>
            <?php else : ?>
            <?php $this->render_textarea_content(); ?>
            <?php endif; ?>
            <?php
            if ( !empty( $description ) ) {
                printf( '<p>%s</p>', $description );
            }
            ?>
        </div>
        <?php
    }

    public function render_admin_table() {
        $key = esc_attr( $this->args['key'] );
        $description = trim( $this->args['description'] );
        $show_color = $this->should_render_with_color();
        ?>
        <tr id="row-<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td>
                <?php if ( $show_color ) : ?>
                <div class="wao-copy-wrap wao-input-has-color wao-textarea-color-wrap">
                    <?php $this->render_textarea_content(); ?>
                    <?php $this->render_color_picker_inline(); ?>
                </div>
                <?php else : ?>
                <?php $this->render_textarea_content(); ?>
                <?php endif; ?>
                <?php
                if ( ! empty( $description ) ) {
                    printf( '<p><code>%s</code></p>', $description );
                }
                ?>
            </td>
        </tr>
        <?php
    }
}
