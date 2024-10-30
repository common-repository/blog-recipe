<?php

/*
 * Plugin Name: Blog Recipe
 * Description: Add the Blog Recipe to the post editor
 * Version: 1.1.1
 * Author: Saskia van de Riet
 * Author URI: http://blogatelier.nl/over-saskia/
 * License: GPL2
 * Text Domain: blog-recipe
 */
$srbl_values = array ();

class SRBL_Ingredient {
	public $id;
	public $value;
	public $title;
	public $tooltip;

	public function __construct ( $id, $value, $title, $tooltip ) {

		$this->id = $id;
		$this->value = $value;
		$this->title = $title;
		$this->tooltip = $tooltip;
	}
}

function srbl_init () {

	global $srbl_values;
	$srbl_values = array ( 
			new SRBL_Ingredient ( 'srbl-title', 'srbl_title', __ ( 'One title', 'blog-recipe' ), __ ( 'Use a working title and finalise the title once you have completed your entire blog post.', 'blog-recipe' ) ),
			new SRBL_Ingredient ( 'srbl-introduction', 'srbl_introduction', __ ( 'One introduction', 'blog-recipe' ), __ ( 'Write one or two lines to describe the main (pain) point of your blog post.', 'blog-recipe' ) ),
			new SRBL_Ingredient ( 'srbl-problem', 'srbl_problem', __ ( 'One problem', 'blog-recipe' ), __ ( 'Describe the main problem (pain, fear, challenge, frustration) of this post\'s topic and explain why it is a problem for your readers, now.', 'blog-recipe' ) ),
			new SRBL_Ingredient ( 'srbl-explanation', 'srbl_explanation', __ ( 'One explanation', 'blog-recipe' ), __ ( 'Describe the consequences for your readers when this problem persists.', 'blog-recipe' ) ),
			new SRBL_Ingredient ( 'srbl-solution', 'srbl_solution', __ ( 'One solution', 'blog-recipe' ), __ ( 'Offer a simple, doable solution to start eliminating that problem, from your expertise. Describe it in simple language, a number of steps, tips or explanation.', 'blog-recipe' ) ),
			new SRBL_Ingredient ( 'srbl-conclusion', 'srbl_conclusion', __ ( 'One conclusion', 'blog-recipe' ), __ ( 'Write one or two lines to briefly summarise what the final conclusion is of what your readers will get out of the solution to their problem.', 'blog-recipe' ) ),
			new SRBL_Ingredient ( 'srbl-cta', 'srbl_cta', __ ( 'One call to action', 'blog-recipe' ), __ ( 'Tell them clearly what exactly you want them to do: leave a comment, share their main insight, list their biggest problem, or ask their most important question.', 'blog-recipe' ) ) 
	);
}
add_action ( 'init', 'srbl_init' );
add_filter ( 'manage_posts_columns', 'srbl_column_head' );
add_action ( 'manage_posts_custom_column', 'srbl_column_content', 10, 2 );

function srbl_column_head ( $defaults ) {

	$defaults [ 'blog_recipe' ] = __ ( 'Blog Recipe', 'blog-recipe' );
	return $defaults;
}

function srbl_column_content ( $column_name, $post_id ) {

	switch ( $column_name ) {
		case 'blog_recipe' :
			srbl_count ( $post_id );
			break;
	}
}

function srbl_count ( $post_id ) {

	$values = get_post_custom ( $post_id );
	global $srbl_values;
	$count = 0;
	
	$tooltip = '--';
	foreach ( $srbl_values as $value ) {
		if ( isset ( $values [ $value->value ] ) && $values [ $value->value ] [ 0 ] == 'on' ) $count++;
		else {
			if ( $tooltip == '--' ) $tooltip = __ ( 'To do:', 'blog-recipe' );
			$tooltip = $tooltip . '<br>' . $value->title;
		}
	}
	
	if ( $count == count ( $srbl_values ) ) _e ( 'Recipe complete!', 'blog-recipe' );
	else {
		echo '<span class="srbl-tooltip">';
		printf ( /* translators: 1: Number done 2: Total count */ __( '%1$s from %2$s ingredients', 'blog-recipe' ), $count, count ( $srbl_values ) );
		echo '<span class="srbl-tooltiptext">' . $tooltip . '</span></span>';
	}
}

add_action ( 'add_meta_boxes', 'srbl_metaBox_add' );
add_action ( 'admin_enqueue_scripts', 'srbl_metaBox_css' );
add_action ( 'save_post', 'srbl_metaBox_save', 10, 2 );

function srbl_metaBox_add () {

	$screens = [ 
			'post' 
	];
	foreach ( $screens as $screen ) {
		add_meta_box ( 'srbl_blog_recipe', __ ( 'Your Blog Recipe', 'blog-recipe' ), 'srbl_metaBox_html', $screen, 'side', 'default' );
	}
}

function srbl_metaBox_html ( $post ) {

	wp_nonce_field ( basename ( __FILE__ ), 'srbl_nonce' );
	?>

<h1><?php _e ( 'INGREDIENTS', 'blog-recipe' ); ?></h1>
<span style="font-weight: bold;"><?php _e ( 'Recipe for 1 blog post of 500 - 700 words', 'blog-recipe' ); ?></span>
<br>

<form id="srbl-ingredients">
<?php
	$values = get_post_custom ( $post->ID );
	global $srbl_values;
	foreach ( $srbl_values as $value ) {
		?>
<input type="checkbox" name="<?php echo $value->id; ?>"id="<?php echo $value->id; ?>"
<?php checked ( isset ( $values [ $value->value ] ) ? esc_attr ( $values [ $value->value ] [ 0 ] ) : '', 'on' ); ?> />
<span class="srbl-tooltip"><?php
		
		echo $value->title;
		?><span class="srbl-tooltiptext"><?php
		
		echo $value->tooltip;
		?></span></span><br>
<?php
	}
	?>
</form>
<hr>
<h1><?php _e ( 'PREPARATION', 'blog-recipe' ); ?></h1>
<span style="font-weight: bold;"><?php _e ( '(The basis)', 'blog-recipe' ); ?></span>
<ol>
	<li><?php _e ( 'Describe the problem.', 'blog-recipe' ); ?></li>
	<li><?php _e ( 'Describe the explanation.', 'blog-recipe' ); ?></li>
	<li><?php _e ( 'Describe the solution.', 'blog-recipe' ); ?></li>
	<li><?php _e ( 'Write the conclusion.', 'blog-recipe' ); ?></li>
	<li><?php _e ( 'Write the call to action.', 'blog-recipe' ); ?></li>
	<li><?php _e ( 'Put the mixture in a saved digital environment as a draft post.', 'blog-recipe' ); ?></li>
</ol>
<span style="font-weight: bold;"><?php _e ( '(Directions)', 'blog-recipe' ); ?></span>
<ol>
	<li><?php _e ( 'Retrieve the mixture by opening your saved draft post.', 'blog-recipe' ); ?></li>
	<li><?php _e ( 'If necessary, polish up the transition between the problem, the
		explanation and the conclusion.', 'blog-recipe' ); ?></li>
	<li><?php _e ( 'Write the introduction.', 'blog-recipe' ); ?></li>
	<li><?php _e ( 'Create a title.', 'blog-recipe' ); ?></li>
	<li><?php _e ( 'Check the completed blog post for spelling.', 'blog-recipe' ); ?></li>
	<li><?php _e ( 'Save the blog post.', 'blog-recipe' ); ?></li>
</ol>
<hr>
<?php _e ( 'Your blog post is now ready for publication.', 'blog-recipe' ); ?>
<?php
}

function srbl_metaBox_css () {

	wp_enqueue_style ( 'srbl_styles', plugin_dir_url ( __FILE__ ) . 'styles.css' );
}

function srbl_metaBox_save ( $post_id, $post ) {

	if ( !isset ( $_POST [ 'srbl_nonce' ] ) || !wp_verify_nonce ( $_POST [ 'srbl_nonce' ], basename ( __FILE__ ) ) ) return $post_id;
	if ( !current_user_can ( get_post_type_object ( $post -> post_type ) -> cap -> edit_post, $post_id ) ) return $post_id;

	global $srbl_values;
	foreach ( $srbl_values as $value )
		srbl_save ( $value -> id, $value -> value, $post_id );
}

function srbl_save ( $input_id, $meta_key, $post_id ) {

	$new_meta_value = ( isset ( $_POST [ $input_id ] ) && $_POST [ $input_id ] ) ? 'on' : 'off';
	$meta_value = get_post_meta ( $post_id, $meta_key, true );

	if ( $new_meta_value && '' == $meta_value ) add_post_meta ( $post_id, $meta_key, $new_meta_value, true );
	elseif ( $new_meta_value && $new_meta_value != $meta_value ) update_post_meta ( $post_id, $meta_key, $new_meta_value );
	elseif ( '' == $new_meta_value && $meta_value ) delete_post_meta ( $post_id, $meta_key, $meta_value );
}

?>
