<?php
/**
 * Title: order-confirmation
 * Slug: happywood/order-confirmation
 * Inserter: no
 */
?>
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->
<!-- wp:pattern {"slug":"happywood/breadcrumbs"} /-->
<!-- wp:group {"tagName":"main","metadata":{"name":"Main"},"align":"full","className":"hw-order-confirmation","style":{"spacing":{"margin":{"top":"0","bottom":"0"},"padding":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|section-l"}}},"backgroundColor":"background","layout":{"type":"constrained"}} -->
<main class="wp-block-group alignfull hw-order-confirmation has-background-background-color has-background" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--0);padding-bottom:var(--wp--preset--spacing--section-l)"><!-- wp:group {"metadata":{"name":"Order Hero Wrapper"},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-l","bottom":"var:preset|spacing|section-l"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
    <div class="wp-block-group alignfull has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--section-l);padding-bottom:var(--wp--preset--spacing--section-l)"><!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|0"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
        <div class="wp-block-group alignwide"><!-- wp:outermost/icon-block {"iconName":"happywood-package","hasNoIconFill":true,"width":"5rem","className":"is-style-style-1","style":{"spacing":{"padding":{"top":"var:preset|spacing|small","bottom":"var:preset|spacing|small","left":"var:preset|spacing|small","right":"var:preset|spacing|small"}}}} -->
            <div class="wp-block-outermost-icon-block is-style-style-1"><div class="icon-container has-no-icon-fill-color" style="width:5rem;padding-top:var(--wp--preset--spacing--small);padding-right:var(--wp--preset--spacing--small);padding-bottom:var(--wp--preset--spacing--small);padding-left:var(--wp--preset--spacing--small);transform:rotate(0deg) scaleX(1) scaleY(1)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><polyline points="3.29 7 12 12 20.71 7"></polyline><path d="m7.5 4.27 9 5.15"></path></svg></div></div>
            <!-- /wp:outermost/icon-block -->

            <!-- wp:woocommerce/order-confirmation-status {"style":{"layout":{"selfStretch":"fit","flexSize":null}}} /-->

            <!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|block-gap"}}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--block-gap)"><!-- wp:woocommerce/order-confirmation-summary /-->

                <!-- wp:woocommerce/order-confirmation-create-account -->
                <div class="wp-block-woocommerce-order-confirmation-create-account alignwide"><!-- wp:heading {"level":2,"fontSize":"x-large"} -->
                    <h2 class="wp-block-heading has-x-large-font-size"><?php esc_html_e('Create an account with Happywood', 'happywood');?></h2>
                    <!-- /wp:heading -->

                    <!-- wp:list {"className":"is-style-checkmark-list"} -->
                    <ul class="wp-block-list is-style-checkmark-list"><!-- wp:list-item -->
                        <li><?php esc_html_e('Faster future purchases', 'happywood');?></li>
                        <!-- /wp:list-item -->

                        <!-- wp:list-item -->
                        <li><?php esc_html_e('Securely save payment info', 'happywood');?></li>
                        <!-- /wp:list-item -->

                        <!-- wp:list-item -->
                        <li><?php esc_html_e('Track orders &amp; view shopping history', 'happywood');?></li>
                        <!-- /wp:list-item --></ul>
                    <!-- /wp:list --></div>
                <!-- /wp:woocommerce/order-confirmation-create-account --></div>
            <!-- /wp:group --></div>
        <!-- /wp:group --></div>
    <!-- /wp:group -->

    <!-- wp:columns {"align":"wide","className":"is-style-has-sidebar-right","style":{"spacing":{"margin":{"top":"var:preset|spacing|section-l"}}},"isStackedOnTablet":true} -->
    <div class="wp-block-columns alignwide is-style-has-sidebar-right is-stacked-on-tablet" style="margin-top:var(--wp--preset--spacing--section-l)"><!-- wp:column {"className":"hw-sidebar-main","style":{"spacing":{"blockGap":"var:preset|spacing|small"}}} -->
        <div class="wp-block-column hw-sidebar-main"><!-- wp:group {"className":"is-style-bordered","style":{"border":{"width":"1px"}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group is-style-bordered" style="border-width:1px"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|small"}},"layout":{"type":"constrained"}} -->
                <div class="wp-block-group"><!-- wp:happywood/info-box {"alignment":"start","style":{"spacing":{"blockGap":"var:preset|spacing|small"}}} -->
                    <div class="wp-block-happywood-info-box is-icon-left is-align-start"><!-- wp:outermost/icon-block {"iconName":"happywood-mail","hasNoIconFill":true,"lock":{"remove":true,"move":true},"className":"is-style-style-1"} -->
                        <div class="wp-block-outermost-icon-block is-style-style-1"><div class="icon-container has-no-icon-fill-color" style="width:48px;transform:rotate(0deg) scaleX(1) scaleY(1)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"></path><rect x="2" y="4" width="20" height="16" rx="2"></rect></svg></div></div>
                        <!-- /wp:outermost/icon-block -->

                        <!-- wp:group {"lock":{"remove":true},"style":{"spacing":{"blockGap":"var:preset|spacing|xxx-small"}}} -->
                        <div class="wp-block-group"><!-- wp:heading {"className":"is-style-default"} -->
                            <h2 class="wp-block-heading is-style-default"><?php esc_html_e('Confirmation Email Sent', 'happywood');?></h2>
                            <!-- /wp:heading -->

                            <!-- wp:paragraph {"className":"is-style-text-lead","fontSize":"small"} -->
                            <p class="is-style-text-lead has-small-font-size"><?php esc_html_e('We\'ve sent a order confirmation to your email.', 'happywood');?></p>
                            <!-- /wp:paragraph --></div>
                        <!-- /wp:group --></div>
                    <!-- /wp:happywood/info-box -->

                    <!-- wp:separator {"backgroundColor":"background"} -->
                    <hr class="wp-block-separator has-text-color has-background-color has-alpha-channel-opacity has-background-background-color has-background"/>
                    <!-- /wp:separator --></div>
                <!-- /wp:group -->

                <!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|x-small"}},"layout":{"type":"constrained"}} -->
                <div class="wp-block-group"><!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"600"}}} -->
                    <p style="font-style:normal;font-weight:600"><?php esc_html_e('Your confirmation email includes:', 'happywood');?></p>
                    <!-- /wp:paragraph -->

                    <!-- wp:list {"happywoodIcon":"check-circle","happywoodIconColor":"body"} -->
                    <ul class="wp-block-list hw-has-icon hw-icon-left hw-icon-check-circle hw-icon-has-color-body"><!-- wp:list-item -->
                        <li><?php esc_html_e('Complete order details and itemized receipt', 'happywood');?></li>
                        <!-- /wp:list-item -->

                        <!-- wp:list-item -->
                        <li><?php esc_html_e('Estimated delivery date and tracking information', 'happywood');?></li>
                        <!-- /wp:list-item -->

                        <!-- wp:list-item -->
                        <li><?php esc_html_e('Payment confirmation and invoice', 'happywood');?></li>
                        <!-- /wp:list-item -->

                        <!-- wp:list-item -->
                        <li><?php esc_html_e('Warranty and product registration information', 'happywood');?></li>
                        <!-- /wp:list-item --></ul>
                    <!-- /wp:list --></div>
                <!-- /wp:group --></div>
            <!-- /wp:group -->

            <!-- wp:group {"className":"is-style-bordered","style":{"border":{"width":"1px"}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group is-style-bordered" style="border-width:1px"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|small"}},"layout":{"type":"constrained"}} -->
                <div class="wp-block-group"><!-- wp:happywood/info-box {"style":{"spacing":{"blockGap":"var:preset|spacing|small"}}} -->
                    <div class="wp-block-happywood-info-box is-icon-left is-align-center"><!-- wp:outermost/icon-block {"iconName":"happywood-package","hasNoIconFill":true,"lock":{"remove":true,"move":true},"className":"is-style-style-1"} -->
                        <div class="wp-block-outermost-icon-block is-style-style-1"><div class="icon-container has-no-icon-fill-color" style="width:48px;transform:rotate(0deg) scaleX(1) scaleY(1)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><polyline points="3.29 7 12 12 20.71 7"></polyline><path d="m7.5 4.27 9 5.15"></path></svg></div></div>
                        <!-- /wp:outermost/icon-block -->

                        <!-- wp:group {"lock":{"remove":true},"style":{"spacing":{"blockGap":"var:preset|spacing|xxx-small"}}} -->
                        <div class="wp-block-group"><!-- wp:heading {"className":"is-style-default"} -->
                            <h2 class="wp-block-heading is-style-default"><?php esc_html_e('Delivery Information', 'happywood');?></h2>
                            <!-- /wp:heading --></div>
                        <!-- /wp:group --></div>
                    <!-- /wp:happywood/info-box -->

                    <!-- wp:separator {"backgroundColor":"background"} -->
                    <hr class="wp-block-separator has-text-color has-background-color has-alpha-channel-opacity has-background-background-color has-background"/>
                    <!-- /wp:separator --></div>
                <!-- /wp:group -->

                <!-- wp:woocommerce/order-confirmation-billing-wrapper -->
                <!-- wp:heading {"level":3} -->
                <h3 class="wp-block-heading"><?php esc_html_e('Billing address', 'happywood');?></h3>
                <!-- /wp:heading -->

                <!-- wp:woocommerce/order-confirmation-billing-address {"lock":{"remove":true}} /-->
                <!-- /wp:woocommerce/order-confirmation-billing-wrapper -->

                <!-- wp:woocommerce/order-confirmation-shipping-wrapper -->
                <!-- wp:heading {"level":3} -->
                <h3 class="wp-block-heading"><?php esc_html_e('Shipping address', 'happywood');?></h3>
                <!-- /wp:heading -->

                <!-- wp:woocommerce/order-confirmation-shipping-address {"lock":{"remove":true}} /-->
                <!-- /wp:woocommerce/order-confirmation-shipping-wrapper -->

                <!-- wp:happywood/order-confirmation-fulfillment /--></div>
            <!-- /wp:group -->



            <!-- wp:woocommerce/order-confirmation-additional-information /-->

            <!-- wp:woocommerce/order-confirmation-downloads-wrapper {"align":"wide"} -->
            <!-- wp:heading {"metadata":{"patternName":"woocommerce/order-confirmation-downloads-heading"},"style":{"typography":{"fontSize":"24px"}}} -->
            <h2 class="wp-block-heading" style="font-size:24px"><?php esc_html_e('Downloads', 'happywood');?></h2>
            <!-- /wp:heading -->

            <!-- wp:woocommerce/order-confirmation-downloads {"lock":{"remove":true}} /-->
            <!-- /wp:woocommerce/order-confirmation-downloads-wrapper -->

            <!-- wp:woocommerce/order-confirmation-additional-fields-wrapper {"align":"wide"} -->
            <!-- wp:heading {"metadata":{"patternName":"woocommerce/order-confirmation-additional-fields-heading"},"style":{"typography":{"fontSize":"24px"}}} -->
            <h2 class="wp-block-heading" style="font-size:24px"><?php esc_html_e('Additional information', 'happywood');?></h2>
            <!-- /wp:heading -->

            <!-- wp:woocommerce/order-confirmation-additional-fields /-->
            <!-- /wp:woocommerce/order-confirmation-additional-fields-wrapper --></div>
        <!-- /wp:column -->

        <!-- wp:column {"className":"hw-sidebar-aside","style":{"spacing":{"blockGap":"var:preset|spacing|small"}}} -->
        <div class="wp-block-column hw-sidebar-aside"><!-- wp:woocommerce/order-confirmation-totals-wrapper {"align":"wide"} -->
            <!-- wp:heading {"metadata":{"patternName":"woocommerce/order-confirmation-totals-heading"},"style":{"typography":{"fontSize":"24px"}}} -->
            <h2 class="wp-block-heading" style="font-size:24px"><?php esc_html_e('Order Summary', 'happywood');?></h2>
            <!-- /wp:heading -->

            <!-- wp:woocommerce/order-confirmation-totals {"lock":{"remove":true}} /-->
            <!-- /wp:woocommerce/order-confirmation-totals-wrapper -->

            <!-- wp:group {"metadata":{"name":"Need Help"},"className":"is-style-bordered hw-order-confirmation-help","style":{"border":{"width":"1px"},"spacing":{"blockGap":"var:preset|spacing|xxx-small"}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group is-style-bordered hw-order-confirmation-help" style="border-width:1px"><!-- wp:happywood/info-box {"style":{"spacing":{"blockGap":"var:preset|spacing|x-small"}}} -->
                <div class="wp-block-happywood-info-box is-icon-left is-align-center"><!-- wp:outermost/icon-block {"iconName":"happywood-phone","hasNoIconFill":true,"width":"2rem","lock":{"remove":true,"move":true},"className":"is-style-default"} -->
                    <div class="wp-block-outermost-icon-block is-style-default"><div class="icon-container has-no-icon-fill-color" style="width:2rem;transform:rotate(0deg) scaleX(1) scaleY(1)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"></path></svg></div></div>
                    <!-- /wp:outermost/icon-block -->

                    <!-- wp:group {"lock":{"remove":true},"style":{"spacing":{"blockGap":"var:preset|spacing|xxx-small"}}} -->
                    <div class="wp-block-group"><!-- wp:paragraph {"className":"is-style-text-heading-like","style":{"typography":{"fontStyle":"normal","fontWeight":"600"}},"fontSize":"x-large"} -->
                        <p class="is-style-text-heading-like has-x-large-font-size" style="font-style:normal;font-weight:600"><?php esc_html_e('Need Help?', 'happywood');?></p>
                        <!-- /wp:paragraph --></div>
                    <!-- /wp:group --></div>
                <!-- /wp:happywood/info-box -->

                <!-- wp:paragraph {"className":"is-style-text-lead","fontSize":"small"} -->
                <p class="is-style-text-lead has-small-font-size"><?php esc_html_e('Our team is here to assist with your order', 'happywood');?></p>
                <!-- /wp:paragraph -->

                <!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|small","bottom":"var:preset|spacing|small"},"blockGap":"var:preset|spacing|0"}},"layout":{"type":"constrained"}} -->
                <div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--small);padding-bottom:var(--wp--preset--spacing--small)"><!-- wp:list {"style":{"elements":{"link":{"color":{"text":"var:preset|color|body"}}}},"textColor":"body","happywoodIcon":"phone","happywoodIconColor":"body"} -->
                    <ul class="wp-block-list has-body-color has-text-color has-link-color hw-has-icon hw-icon-left hw-icon-phone hw-icon-has-color-body"><!-- wp:list-item -->
                        <li><?php /* Translators: 1. is the start of a 'a' HTML element, 2. is the end of a 'a' HTML element */ 
echo sprintf( esc_html__( '%1$sREPLACE_ME phone%2$s', 'happywood' ), '<a href="' . esc_url( 'tel:REPLACE_ME' ) . '">', '</a>' ); ?></li>
                        <!-- /wp:list-item --></ul>
                    <!-- /wp:list -->

                    <!-- wp:list {"style":{"elements":{"link":{"color":{"text":"var:preset|color|body"}}}},"textColor":"body","happywoodIcon":"mail","happywoodIconColor":"body"} -->
                    <ul class="wp-block-list has-body-color has-text-color has-link-color hw-has-icon hw-icon-left hw-icon-mail hw-icon-has-color-body"><!-- wp:list-item -->
                        <li><?php /* Translators: 1. is the start of a 'a' HTML element, 2. is the end of a 'a' HTML element */ 
echo sprintf( esc_html__( '%1$sREPLACE_ME@example.com%2$s', 'happywood' ), '<a href="' . esc_url( 'mailto:REPLACE_ME@example.com' ) . '">', '</a>' ); ?></li>
                        <!-- /wp:list-item --></ul>
                    <!-- /wp:list --></div>
                <!-- /wp:group -->

                <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center","orientation":"vertical","verticalAlignment":"center"}} -->
                <div class="wp-block-buttons"><!-- wp:button {"width":100,"className":"is-style-light","style":{"typography":{"textAlign":"left"}},"happywoodIcon":"arrow-right"} -->
                    <div class="wp-block-button has-custom-width wp-block-button__width-100 is-style-light hw-has-icon hw-icon-left hw-icon-arrow-right"><a class="wp-block-button__link has-text-align-left wp-element-button" href="/contact/"><?php esc_html_e('Contact Us', 'happywood');?></a></div>
                    <!-- /wp:button --></div>
                <!-- /wp:buttons --></div>
            <!-- /wp:group -->

            <!-- wp:group {"metadata":{"name":"Quick Actions"},"className":"is-style-bordered hw-order-confirmation-actions","style":{"border":{"width":"1px"},"spacing":{"blockGap":"var:preset|spacing|small"}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group is-style-bordered hw-order-confirmation-actions" style="border-width:1px"><!-- wp:happywood/info-box {"style":{"spacing":{"blockGap":"var:preset|spacing|x-small"}}} -->
                <div class="wp-block-happywood-info-box is-icon-left is-align-center"><!-- wp:outermost/icon-block {"iconName":"happywood-zap","hasNoIconFill":true,"width":"2rem","lock":{"remove":true,"move":true},"className":"is-style-default"} -->
                    <div class="wp-block-outermost-icon-block is-style-default"><div class="icon-container has-no-icon-fill-color" style="width:2rem;transform:rotate(0deg) scaleX(1) scaleY(1)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path></svg></div></div>
                    <!-- /wp:outermost/icon-block -->

                    <!-- wp:group {"lock":{"remove":true},"style":{"spacing":{"blockGap":"var:preset|spacing|xxx-small"}}} -->
                    <div class="wp-block-group"><!-- wp:paragraph {"className":"is-style-text-heading-like","style":{"typography":{"fontStyle":"normal","fontWeight":"600"}},"fontSize":"x-large"} -->
                        <p class="is-style-text-heading-like has-x-large-font-size" style="font-style:normal;font-weight:600"><?php esc_html_e('Quick Actions', 'happywood');?></p>
                        <!-- /wp:paragraph --></div>
                    <!-- /wp:group --></div>
                <!-- /wp:happywood/info-box -->

                <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center","orientation":"vertical","verticalAlignment":"center"}} -->
                <div class="wp-block-buttons"><!-- wp:button {"width":100,"className":"is-style-light","style":{"typography":{"textAlign":"left"}},"happywoodIcon":"zap","happywoodPrint":true} -->
                    <div class="wp-block-button has-custom-width wp-block-button__width-100 is-style-light hw-has-icon hw-icon-left hw-icon-zap"><a class="wp-block-button__link has-text-align-left wp-element-button" href="#" data-hw-print-order="1"><?php esc_html_e('Print Order Summary', 'happywood');?></a></div>
                    <!-- /wp:button --></div>
                <!-- /wp:buttons --></div>
            <!-- /wp:group --></div>
        <!-- /wp:column --></div>
    <!-- /wp:columns --></main>
<!-- /wp:group -->
<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
