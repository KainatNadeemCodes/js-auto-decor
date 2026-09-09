<?php
/**
 * Ajax Search.
 *
 * @package ShopPress
 */

defined( 'ABSPATH' ) || exit;

$terms  = get_terms( array( 'taxonomy' => 'product_cat' ) );
$is_cat = $args['cat'] === 'yes' ? 'sp-ajax-cat' : '';
?>

<div id="sp-ajax-search-warp" class="<?php echo esc_attr( $is_cat ); ?>">
	<?php if ( $args['cat'] === 'yes' ) : ?>
		<select name="cat">
			<option value=""><?php echo esc_html( $args['c_text'] ); ?></option>
			<?php foreach ( $terms as $key => $term ) : ?>

				<?php if ( 'Uncategorized' === $term->name ) : ?>
					<?php continue; ?>
				<?php endif; ?>

				<option value="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></option>
			<?php endforeach; ?>
		</select>
	<?php endif; ?>
	<div class="sp-ajax-search">
		<form action="#">
			<input type="text" class="sp-ajax-search-input" name="search" placeholder="<?php echo esc_attr( $args['s_placeholder'] ); ?>">
		</form>
	</div>
	<div class="sp-ajax-search-result" data-limit="<?php echo esc_attr( $args['limit'] ); ?>"></div>
</div>
