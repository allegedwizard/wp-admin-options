# Changelog

All notable changes to this project will be documented in this file.

## [dev]

### Added
- `seconds` parameter on `DateTimeOption` (default **off**). With it off, the time input runs at minute granularity (`HH:MM`); pass `'seconds' => true` to allow `HH:MM:SS` (adds `step="1"` and formats the value as `H:i:s`).
- FOUC guard rule `[v-if], [v-for] { display: none }` at the top of `assets/css/wp-admin-options.css`. Vue mounts on `window.load`, before which the browser renders each field's template literally -- "No attachment is assigned" appearing alongside an empty `wao-attachment-item` placeholder, mustache text like `{{ item.title }}` flashing through, etc. Vue strips `v-if` / `v-for` attributes during template compilation, so the rule self-disarms the moment a field finishes mounting (no JS hook needed). Covers every Vue-using field: AttachmentOption, SelectOption, PostType/Taxonomy/UserSelectOption, ExampleJsonOption, ExampleJsonMediaOption, DateTime/DateTimeRange/DurationOption.
- `with_color` parameter on `InputOption` and `TextareaOption` -- renders an inline color picker visually attached to the input/textarea (shared border, height, and corner radius). Accepts `true` / `'default'` (wp-color-picker / Iris), `'spectrum'`, or `'swatches'`. Picker value is saved as a sibling meta key suffixed `_color` (no extra wiring required).
- `with_color_value` and `with_color_swatches` parameters on `AbstractAdminOption` for the inline color picker's initial value and preset swatch array.
- Color picker helpers on `AbstractAdminOption`: `resolve_color_picker_type()`, `should_render_with_color()`, `render_color_picker_inline()`, `enqueue_color_picker_assets()`, `render_color_picker_init()`.
- CSS for the inline color picker (`.wao-input-has-color`, `.wao-input-color-inline`, `.wao-textarea-color-wrap`) covering wp-color-picker, spectrum, and swatches modes -- all variants share the input's border, height, and right-edge corner radii.
- Photoshop-style transparency checkerboard on the spectrum preview swatch and on `AttachmentOption` image thumbnails, so transparent images and translucent colors are clearly visible against light card backgrounds.
- Action hooks on `OptionsContainer`: `wao_options_container_before_render`, `wao_options_container_body_start`, `wao_options_container_body_end`, `wao_options_container_after_render` -- all pass the container's `$args` for injection of header/footer markup.
- Help tooltip viewport-anchored positioning -- on hover/focus, `.wao-help-text` switches to `position: fixed` with coords derived from the icon's bounding rect. Escapes any `overflow: hidden` ancestor (e.g. `.wao-container`) that would otherwise clip the tooltip. Auto-flips to the left of the icon (with a `.wao-help-text-flipped` caret) when it would overflow the right viewport edge.
- `multiple` parameter on `DateTimeOption` for storing an array of date/time values; renders as a sortable, drag-and-drop reorderable list with add/remove controls
- `DateTimeRangeOption` field for capturing one or more `[start_date, end_date]` ranges; supports `multiple` for sortable lists of ranges, single mode is constrained to one range
- Array value validation (`render_array_error()`) on `DateTimeOption` when `multiple` is enabled and on `DateTimeRangeOption` (always)
- `DateTimeRangeOption` JS handler with shared list/drag mixins for range items
- CSS for `.wao-datetime-range` and `.wao-datetime-range-field` (stacked Start/End rows with uppercase labels)
- `OptionsContainer` class for grouping options into collapsible, styled card sections with headers
- Closure-based `fields` parameter on `OptionsContainer` for direct class instantiation inside output-buffered callbacks
- Password reveal toggle button on `InputOption` (type `password`) with `prevent_reveal` parameter to disable
- Telephone input masking via `telephone_format` parameter (`US`, `International`, or custom mask pattern)
- `digits_only` parameter for tel inputs — displays formatted value but submits raw digits via hidden input
- `PasswordReveal` JS handler for toggling password visibility
- `TelMask` JS handler for input formatting with cursor position preservation
- `OptionsContainer` JS handler for collapse/expand with localStorage persistence
- HSV color picker with alpha channel support (`swatches` type on `ColorOption`)
- `color_swatches` parameter on `ColorOption` for preset swatch arrays
- Select2 placeholder support on `SelectOption` (single and multiple modes)
- `render_test_options()` on `Bootstrap\WPAdminOptions` with comprehensive examples for all option types
- `help` tooltip attributes across all test option fields
- `Bootstrap\WPAdminOptions` class for centralized CDN asset enqueuing (Vue.js 3.5.22, Select2 4.0.13)
- Auto-initialization of assets via `AbstractAdminOption` on first use
- `assets/css/wp-admin-options.css` stylesheet with `wao-` prefixed utility classes and Tailwind UI-inspired styling
- `assets/js/wp-admin-options.js` with `window.WPAdminOptions` namespace for all option field scripts
- Shared JS helpers: `_dragMixin()`, `_itemMixin()`, `_select2Mount()`, `_merge()` for composable Vue option objects
- `_itemMixin` accepts configurable items property name for flexible list management (e.g. `'items'`, `'ids'`)
- `enable_copy` parameter on `InputOption` with clipboard copy button (auto-enabled for readonly inputs)
- Custom duration input mode on `DurationOption` with preset/custom toggle
- Drag-and-drop reordering with visual drop indicator on all sortable item lists
- Compact button groups (`.wao-action-group`) for action buttons
- Inline field labels (`.wao-field`, `.wao-field-label`) for ExampleJson option types
- `render_taxonomy_field()` implementations for BooleanCheckboxOption, ColorOption, EditorOption, HtmlOption
- `enable_test_mode()` for inline CSS and JS output via `<style>` and `<script>` tags
- Array value validation with red error banner (`render_array_error()`) for options requiring array input
- `.wao-error` CSS class for error banner styling
- CSS for Options Container (`.wao-container`, `.wao-collapsed`), password reveal (`.wao-reveal-btn`), and color picker panel (`.wao-cp-*`)
- Auto-detection of external package path for inline asset fallback when outside WP installation

### Changed
- Upgraded from Vue.js 2 to Vue.js 3 (`new Vue()` → `Vue.createApp().mount()`)
- Migrated all option classes to Vue 3 compatible syntax: SelectOption, PostTypeSelectOption, TaxonomySelectOption, UserSelectOption, AttachmentOption, DateTimeOption, DurationOption, ExampleJsonOption, ExampleJsonMediaOption
- Fixed `v-for` destructuring to use parenthesized syntax (`v-for="(item, i) in items"`)
- Moved `before_admin_option` / `after_admin_option` hooks into `AbstractAdminOption::render()` dispatcher, removed from subclasses
- Extracted shared rendering into helper methods: `TextareaOption::render_textarea_content()`, `AttachmentOption::render_attachment_content()`
- Reduced code duplication between `render_admin_table()` and `render_taxonomy_field()` contexts
- Extracted all inline `<script>` blocks into `assets/js/wp-admin-options.js` with `WPAdminOptions` namespace
- Each PHP script call wrapped in `window.addEventListener('load', ...)` with consolidated `json_encode($args)` for clean data passing
- Removed all redundant `$(document).ready` / `jQuery(document).ready` wrappers from JS (window load guarantees DOM readiness)
- ExampleJson options refactored as extension pattern examples using `WPAdminOptions._merge()` and `_dragMixin()`/`_itemMixin()`
- Disabled drag-and-drop attributes on singular AttachmentOption (non-multiple mode)
- `ColorOption` refactored to support `swatches` type alongside existing `spectrum` and default WordPress pickers

### Fixed
- `DateTimeRangeOption` time inputs were uneditable when the stored value carried seconds, the same bug fixed earlier in `DateTimeOption`: the four `<input type="time">` elements rendered with no `step` but were seeded with `H:i:s`, so the browser rejected the value ("Please select a valid value. The two nearest valid values are ..."). Ported the `seconds` parameter (default off) and the `time_step_attr()` / `time_format()` helpers, so the inputs default to minute granularity (`H:i`, no `step`) and only emit `step="1"` + `H:i:s` when `seconds` is enabled.
- `DateTimeOption` time input was uneditable when the stored value carried seconds: it rendered `<input type="time">` with no `step` but seeded the value as `H:i:s`, so the browser rejected it ("Please select a valid value. The two nearest valid values are ..."). The input now defaults to minute granularity (`H:i`, no `step`) and only emits `step="1"` + `H:i:s` when `seconds` is enabled. The saved value also normalizes a trailing `HH:MM` to `HH:MM:00`, so a no-seconds entry stores a full timestamp.
- `ColorOption` (spectrum type) -- changed `preferredFormat` from `'hex'` to `'rgb'`. Spectrum 1.8.x's tinycolor emits hex8 in `#AARRGGBB` (Java/Android) byte order, which CSS interprets as `#RRGGBBAA`, so saved values rendered as a completely different color than the user picked. `rgb` is unambiguous and supports the alpha channel via `rgba()`.
- `ExampleJsonMediaOption` -- Vue's `:value` binding updates the DOM property but doesn't fire native `input`/`change` events, so external listeners (live previews, form-state trackers) never saw the value change. The component now dispatches both events on the hidden input after the `json` watcher commits.
- Missing `</script>` closing tags in SelectOption and PostTypeSelectOption
- `.wao-vue-wrap { display: none !important }` preventing Vue multi-select components from rendering
- Missing `@click` handler on ExampleJsonMediaOption "Remove Image" button
- Uninitialized `$date` variable in DateTimeOption when value is empty
- Color picker panel clipped by `overflow: hidden` inside OptionsContainer -- body now uses `overflow: visible` when expanded

### Changed (Refactor)
- Rebranded namespace from `Zawntech\WPAdminOptions` to `AllegedWizard\WPAdminOptions`
- Reorganized classes into sub-namespaces: `Fields\`, `Structure\`, `Bootstrap\`, `Helpers\`
- Moved all option classes (`*Option.php`) into `src/Fields/`
- Moved `OptionsContainer` into `src/Structure/`
- Extracted `render_test_options()` into `Helpers\RenderTestFields` class with proxy in `Bootstrap\WPAdminOptions`
- Updated `composer.json` package name and PSR-4 autoload mapping

## [1.0.0]

### Added
- `AbstractAdminOption` base class with admin-table and taxonomy rendering contexts
- `InputOption` for text inputs
- `TextareaOption` with configurable rows
- `EditorOption` using WordPress WYSIWYG editor
- `HtmlOption` for raw HTML display
- `BooleanCheckboxOption` with 1/0 storage
- `ColorOption` with WordPress color picker and optional Spectrum.js driver
- `SelectOption` with single and multiple support via Vue.js
- `PostTypeSelectOption` for post selection by post type
- `TaxonomySelectOption` for term selection by taxonomy
- `UserSelectOption` with role filtering
- `AttachmentOption` for media library selection via Vue.js
- `DateTimeOption` for date and time inputs
- `DurationOption` for hours/minutes selection
- `ExampleJsonOption` for editable JSON arrays
- `ExampleJsonMediaOption` for JSON media arrays with reordering
- Help tooltip system with CSS injection
- `before_admin_option` and `after_admin_option` action hooks
- Taxonomy context rendering for all field types
- Select2 integration for enhanced dropdowns
- Placeholder attribute support
- Allow `'0'` string values in inputs
