<?php
/**
 * Plugin Name: Tablepro
 * Description: جدول‌ساز حرفه‌ای بر پایه المنتور
 * Version: 1.0.0
 * Author: AliRezaMoghimian.ir
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ثبت استایل‌های پایه و ریسپانسیو
add_action( 'elementor/frontend/after_register_styles', function() {
    wp_register_style( 'pro-col-table-style', false );
    wp_add_inline_style( 'pro-col-table-style', '
        .pct-wrapper{width:100%;max-width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;margin:20px 0;direction:rtl;font-family:inherit}
        .pct-table{width:100%;border-collapse:collapse;overflow:hidden;min-width:450px}
        .pct-table th{font-weight:700;vertical-align:middle;transition:all .2s ease}
        .pct-table td{vertical-align:middle;transition:all .2s ease}
        .pct-table th *,.pct-table td *{margin:0;padding:0;font-weight:inherit;font-size:inherit;color:inherit;line-height:inherit}
        .pct-table tbody tr{transition:all .2s ease}
    ' );
} );

function register_pro_column_table_widget( $widgets_manager ) {
    if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
        return;
    }

    class Pro_Column_Table_Widget extends \Elementor\Widget_Base {

        public function get_name() {
            return 'pro_column_table_v9';
        }

        public function get_title() {
            return __( 'جدول ساز حرفه ای ' );
        }

        public function get_icon() {
            return 'eicon-table';
        }

        public function get_categories() {
            return [ 'general' ];
        }

        public function get_style_depends() {
            return [ 'pro-col-table-style' ];
        }

        protected function register_controls() {

            // ==========================================
            // ============ تب محتوا (Content) ==========
            // ==========================================

            $this->start_controls_section(
                'section_general',
                [
                    'label' => __( 'تنظیمات کلی جدول', 'pro-col-table' ),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                ]
            );

            $this->add_control(
                'cols_count',
                [
                    'label' => __( 'تعداد ستون‌های جدول', 'pro-col-table' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => [
                        '1' => '۱ ستون',
                        '2' => '۲ ستون',
                        '3' => '۳ ستون',
                        '4' => '۴ ستون',
                        '5' => '۵ ستون',
                        '6' => '۶ ستون',
                    ],
                    'default' => '2',
                    'description' => __( 'با انتخاب تعداد ستون، بخش تنظیمات محتوای هر ستون در پایین همین تب ظاهر می‌شود.', 'pro-col-table' ),
                ]
            );

            $this->add_control(
                'header_html_tag',
                [
                    'label' => __( 'تگ HTML عناوین ستون‌ها (Header)', 'pro-col-table' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => [
                        'span' => 'span', 'div' => 'div', 'p' => 'p',
                        'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6',
                    ],
                    'default' => 'span',
                ]
            );

            $this->end_controls_section();

            // --- ایجاد بخش محتوای اختصاصی برای هر ستون (۱ تا ۶) ---
            $default_headers = [
                1 => 'ناحیه درمان',
                2 => 'هزینه (میلیون تومان)',
                3 => 'توضیحات تکمیلی',
                4 => 'ستون ۴',
                5 => 'ستون ۵',
                6 => 'ستون ۶',
            ];

            $default_rows = [
                1 => [
                    [ 'cell_text' => 'اندولیفت صورت' ],
                    [ 'cell_text' => 'اندولیفت غبغب' ],
                    [ 'cell_text' => 'پکیج VIP صورت و گردن' ],
                ],
                2 => [
                    [ 'cell_text' => '۲۵' ],
                    [ 'cell_text' => '۱۵' ],
                    [ 'cell_text' => '۳۵' ],
                ],
                3 => [
                    [ 'cell_text' => '۱ جلسه' ],
                    [ 'cell_text' => '۱ جلسه' ],
                    [ 'cell_text' => 'تخفیف ویژه' ],
                ],
            ];

            for ( $i = 1; $i <= 6; $i++ ) {
                // شرط نمایش بر اساس تعداد ستون انتخابی
                $allowed_cols = [];
                for ( $j = $i; $j <= 6; $j++ ) {
                    $allowed_cols[] = (string) $j;
                }

                $this->start_controls_section(
                    'section_col_content_' . $i,
                    [
                        'label' => __( 'محتوای ستون ' . $i, 'pro-col-table' ),
                        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                        'condition' => [ 'cols_count' => $allowed_cols ],
                    ]
                );

                $this->add_control(
                    'col_title_' . $i,
                    [
                        'label' => __( 'عنوان ستون ' . $i . ' (در هدر)', 'pro-col-table' ),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'default' => $default_headers[$i],
                        'label_block' => true,
                    ]
                );

                $this->add_control(
                    'col_tag_' . $i,
                    [
                        'label' => __( 'تگ HTML محتوای این ستون', 'pro-col-table' ),
                        'type' => \Elementor\Controls_Manager::SELECT,
                        'options' => [
                            'span' => 'span', 'div' => 'div', 'p' => 'p', 'strong' => 'strong (ضخیم)',
                            'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6',
                        ],
                        'default' => 'span',
                    ]
                );

                $repeater = new \Elementor\Repeater();

                $repeater->add_control(
                    'cell_text',
                    [
                        'label' => __( 'متن سلول (ردیف)', 'pro-col-table' ),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'default' => __( 'مقدار جدید', 'pro-col-table' ),
                        'label_block' => true,
                    ]
                );

                $this->add_control(
                    'col_items_' . $i,
                    [
                        'label' => __( 'ردیف‌های ستون ' . $i, 'pro-col-table' ),
                        'type' => \Elementor\Controls_Manager::REPEATER,
                        'fields' => $repeater->get_controls(),
                        'default' => isset( $default_rows[$i] ) ? $default_rows[$i] : [
                            [ 'cell_text' => 'ردیف ۱' ],
                            [ 'cell_text' => 'ردیف ۲' ],
                            [ 'cell_text' => 'ردیف ۳' ],
                        ],
                        'title_field' => '{{{ cell_text }}}',
                    ]
                );

                $this->end_controls_section();
            }

            // ==========================================
            // ============ تب استایل (Style) ===========
            // ==========================================

            // --- ۱. ابعاد و چیدمان اختصاصی هر ستون ---
            $this->start_controls_section(
                'style_columns_layout',
                [
                    'label' => __( 'ابعاد و استایل مجزای ستون‌ها', 'pro-col-table' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            for ( $i = 1; $i <= 6; $i++ ) {
                $allowed_cols = [];
                for ( $j = $i; $j <= 6; $j++ ) {
                    $allowed_cols[] = (string) $j;
                }

                $this->add_control(
                    'heading_style_col_' . $i,
                    [
                        'label' => __( 'تنظیمات ستون ' . $i, 'pro-col-table' ),
                        'type' => \Elementor\Controls_Manager::HEADING,
                        'separator' => ( $i > 1 ) ? 'before' : 'none',
                        'condition' => [ 'cols_count' => $allowed_cols ],
                    ]
                );

                $this->add_responsive_control(
                    'col_width_' . $i,
                    [
                        'label' => __( 'عرض ستون ' . $i, 'pro-col-table' ),
                        'type' => \Elementor\Controls_Manager::SLIDER,
                        'size_units' => [ '%', 'px' ],
                        'range' => [
                            '%' => [ 'min' => 5, 'max' => 100 ],
                            'px' => [ 'min' => 50, 'max' => 800 ],
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .pct-table th.pct-col-' . $i => 'width: {{SIZE}}{{UNIT}};',
                            '{{WRAPPER}} .pct-table td.pct-col-' . $i => 'width: {{SIZE}}{{UNIT}};',
                        ],
                        'condition' => [ 'cols_count' => $allowed_cols ],
                    ]
                );

                $this->add_control(
                    'col_align_' . $i,
                    [
                        'label' => __( 'چیدمان متن ستون ' . $i, 'pro-col-table' ),
                        'type' => \Elementor\Controls_Manager::CHOOSE,
                        'options' => [
                            'right' => [ 'title' => 'راست', 'icon' => 'eicon-text-align-right' ],
                            'center' => [ 'title' => 'وسط', 'icon' => 'eicon-text-align-center' ],
                            'left' => [ 'title' => 'چپ', 'icon' => 'eicon-text-align-left' ],
                        ],
                        'default' => ( $i === 1 ) ? 'right' : 'center',
                        'selectors' => [
                            '{{WRAPPER}} .pct-table th.pct-col-' . $i => 'text-align: {{VALUE}};',
                            '{{WRAPPER}} .pct-table td.pct-col-' . $i => 'text-align: {{VALUE}};',
                        ],
                        'condition' => [ 'cols_count' => $allowed_cols ],
                    ]
                );

                $this->add_control(
                    'col_color_' . $i,
                    [
                        'label' => __( 'رنگ متن اختصاصی ستون ' . $i, 'pro-col-table' ),
                        'type' => \Elementor\Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .pct-table td.pct-col-' . $i . ', {{WRAPPER}} .pct-table td.pct-col-' . $i . ' *' => 'color: {{VALUE}};',
                        ],
                        'condition' => [ 'cols_count' => $allowed_cols ],
                    ]
                );
            }

            $this->end_controls_section();

            // --- ۲. استایل کلی کادر جدول ---
            $this->start_controls_section(
                'style_table_box',
                [
                    'label' => __( 'کادر و حاشیه کلی جدول', 'pro-col-table' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'table_radius',
                [
                    'label' => __( 'انحنای گوشه‌های جدول (Border Radius)', 'pro-col-table' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'default' => [ 'top' => '12', 'right' => '12', 'bottom' => '12', 'left' => '12', 'unit' => 'px' ],
                    'selectors' => [
                        '{{WRAPPER}} .pct-table' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'table_outer_border',
                    'label' => __( 'حاشیه دور کل جدول', 'pro-col-table' ),
                    'selector' => '{{WRAPPER}} .pct-table',
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name' => 'table_box_shadow',
                    'label' => __( 'سایه جدول', 'pro-col-table' ),
                    'selector' => '{{WRAPPER}} .pct-table',
                ]
            );

            $this->end_controls_section();

            // --- ۳. استایل هدر جدول ---
            $this->start_controls_section(
                'style_header',
                [
                    'label' => __( 'استایل هدر (عناوین بالا)', 'pro-col-table' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'header_bg',
                [
                    'label' => __( 'رنگ پس‌زمینه هدر', 'pro-col-table' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#b89779',
                    'selectors' => [ '{{WRAPPER}} .pct-table thead th' => 'background-color: {{VALUE}};' ],
                ]
            );

            $this->add_control(
                'header_text_color',
                [
                    'label' => __( 'رنگ متن هدر', 'pro-col-table' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                    'selectors' => [ '{{WRAPPER}} .pct-table thead th, {{WRAPPER}} .pct-table thead th *' => 'color: {{VALUE}};' ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'header_typography',
                    'label' => __( 'تایپوگرافی هدر', 'pro-col-table' ),
                    'selector' => '{{WRAPPER}} .pct-table thead th',
                ]
            );

            $this->add_responsive_control(
                'header_padding',
                [
                    'label' => __( 'فاصله داخلی هدر (Padding)', 'pro-col-table' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'default' => [ 'top' => '16', 'right' => '20', 'bottom' => '16', 'left' => '20', 'unit' => 'px' ],
                    'selectors' => [
                        '{{WRAPPER}} .pct-table thead th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'header_cell_border',
                    'label' => __( 'خطوط جداکننده هدر', 'pro-col-table' ),
                    'selector' => '{{WRAPPER}} .pct-table thead th',
                ]
            );

            $this->end_controls_section();

            // --- ۴. استایل سطرها و سلول‌ها ---
            $this->start_controls_section(
                'style_rows',
                [
                    'label' => __( 'استایل سطرها و سلول‌ها', 'pro-col-table' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->start_controls_tabs( 'tabs_row_styles' );

            // حالت عادی
            $this->start_controls_tab( 'tab_row_normal', [ 'label' => __( 'عادی', 'pro-col-table' ) ] );

            $this->add_control(
                'row_bg_color',
                [
                    'label' => __( 'رنگ پس‌زمینه سطرها', 'pro-col-table' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                    'selectors' => [ '{{WRAPPER}} .pct-table tbody tr' => 'background-color: {{VALUE}};' ],
                ]
            );

            $this->add_control(
                'row_text_color',
                [
                    'label' => __( 'رنگ متن عمومی سطرها', 'pro-col-table' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#222222',
                    'selectors' => [ '{{WRAPPER}} .pct-table tbody td, {{WRAPPER}} .pct-table tbody td *' => 'color: {{VALUE}};' ],
                ]
            );

            $this->end_controls_tab();

            // حالت یکی در میان
            $this->start_controls_tab( 'tab_row_striped', [ 'label' => __( 'یکی در میان', 'pro-col-table' ) ] );

            $this->add_control(
                'row_even_bg',
                [
                    'label' => __( 'پس‌زمینه سطرهای زوج', 'pro-col-table' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [ '{{WRAPPER}} .pct-table tbody tr:nth-child(even)' => 'background-color: {{VALUE}};' ],
                ]
            );

            $this->add_control(
                'row_odd_bg',
                [
                    'label' => __( 'پس‌زمینه سطرهای فرد', 'pro-col-table' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [ '{{WRAPPER}} .pct-table tbody tr:nth-child(odd)' => 'background-color: {{VALUE}};' ],
                ]
            );

            $this->end_controls_tab();

            // حالت هاور
            $this->start_controls_tab( 'tab_row_hover', [ 'label' => __( 'هاور (موس)', 'pro-col-table' ) ] );

            $this->add_control(
                'row_hover_bg',
                [
                    'label' => __( 'پس‌زمینه هنگام هاور', 'pro-col-table' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#f9f6f2',
                    'selectors' => [ '{{WRAPPER}} .pct-table tbody tr:hover' => 'background-color: {{VALUE}};' ],
                ]
            );

            $this->add_control(
                'row_hover_color',
                [
                    'label' => __( 'رنگ متن هنگام هاور', 'pro-col-table' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [ '{{WRAPPER}} .pct-table tbody tr:hover td, {{WRAPPER}} .pct-table tbody tr:hover td *' => 'color: {{VALUE}};' ],
                ]
            );

            $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_control( 'hr_rows', [ 'type' => \Elementor\Controls_Manager::DIVIDER ] );

            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'cell_typography',
                    'label' => __( 'تایپوگرافی محتوای جدول', 'pro-col-table' ),
                    'selector' => '{{WRAPPER}} .pct-table tbody td',
                ]
            );

            $this->add_responsive_control(
                'cell_padding',
                [
                    'label' => __( 'فاصله داخلی سلول‌ها (Padding)', 'pro-col-table' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'default' => [ 'top' => '14', 'right' => '20', 'bottom' => '14', 'left' => '20', 'unit' => 'px' ],
                    'selectors' => [
                        '{{WRAPPER}} .pct-table tbody td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_control(
                'cell_border_color',
                [
                    'label' => __( 'رنگ خطوط جداکننده جدول (Borders)', 'pro-col-table' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#eaeaea',
                    'selectors' => [
                        '{{WRAPPER}} .pct-table tbody td' => 'border: 1px solid {{VALUE}};',
                        '{{WRAPPER}} .pct-table thead th' => 'border-left: 1px solid rgba(255,255,255,0.2);',
                    ],
                ]
            );

            $this->end_controls_section();
        }

        protected function render() {
            $settings = $this->get_settings_for_display();
            $cols_count = (int) $settings['cols_count'];
            $h_tag = \Elementor\Utils::validate_html_tag( $settings['header_html_tag'] );

            // جمع‌آوری داده‌های هر ستون و پیدا کردن بیشترین تعداد سطر
            $columns_data = [];
            $col_tags = [];
            $max_rows = 0;

            for ( $i = 1; $i <= $cols_count; $i++ ) {
                $items = isset( $settings['col_items_' . $i] ) && is_array( $settings['col_items_' . $i] ) ? $settings['col_items_' . $i] : [];
                $columns_data[$i] = $items;
                $col_tags[$i] = \Elementor\Utils::validate_html_tag( $settings['col_tag_' . $i] );

                $count = count( $items );
                if ( $count > $max_rows ) {
                    $max_rows = $count;
                }
            }
            ?>
            <div class="pct-wrapper">
                <table class="pct-table">
                    <thead>
                        <tr>
                            <?php for ( $i = 1; $i <= $cols_count; $i++ ) : 
                                $header_title = isset( $settings['col_title_' . $i] ) ? $settings['col_title_' . $i] : '';
                            ?>
                                <th class="pct-col-<?php echo esc_attr( $i ); ?>">
                                    <<?php echo $h_tag; ?>><?php echo esc_html( $header_title ); ?></<?php echo $h_tag; ?>>
                                </th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( $max_rows > 0 ) : ?>
                            <?php for ( $row_idx = 0; $row_idx < $max_rows; $row_idx++ ) : ?>
                                <tr>
                                    <?php for ( $col_idx = 1; $col_idx <= $cols_count; $col_idx++ ) : 
                                        $cell_value = isset( $columns_data[$col_idx][$row_idx]['cell_text'] ) ? $columns_data[$col_idx][$row_idx]['cell_text'] : '';
                                        $c_tag = $col_tags[$col_idx];
                                    ?>
                                        <td class="pct-col-<?php echo esc_attr( $col_idx ); ?>">
                                            <<?php echo $c_tag; ?>><?php echo esc_html( $cell_value ); ?></<?php echo $c_tag; ?>>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
    }

    if ( is_object( $widgets_manager ) ) {
        $widgets_manager->register( new \Pro_Column_Table_Widget() );
    }
}
add_action( 'elementor/widgets/register', 'register_pro_column_table_widget' );