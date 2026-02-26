<?php

namespace ATESO_ENG\Admin;

use ATESO_ENG\Base\BaseController;
use ATESO_ENG\Database\TermRepository;

class AdminMenu extends BaseController {

	public function register() {
		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
	}

	/**
	 * Handle delete and bulk actions from the list table.
	 */
	public function handle_actions() {
		// Single delete.
		if ( isset( $_GET['page'], $_GET['action'], $_GET['id'] )
			&& 'ateso-dictionary' === $_GET['page']
			&& 'delete' === $_GET['action']
		) {
			$id = absint( $_GET['id'] );
			check_admin_referer( 'delete_entry_' . $id );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'Unauthorized.' );
			}

			$term_repo = new TermRepository();
			$term_repo->delete( $id );

			wp_redirect( admin_url( 'admin.php?page=ateso-dictionary&deleted=1' ) );
			exit;
		}

		// Bulk delete.
		if ( isset( $_POST['page'], $_POST['action'] )
			&& 'ateso-dictionary' === $_POST['page']
			&& 'delete' === $_POST['action']
			&& ! empty( $_POST['entry_ids'] )
		) {
			check_admin_referer( 'bulk-dictionary_entries' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'Unauthorized.' );
			}

			$term_repo = new TermRepository();
			$ids       = array_map( 'absint', $_POST['entry_ids'] );

			foreach ( $ids as $id ) {
				$term_repo->delete( $id );
			}

			wp_redirect( admin_url( 'admin.php?page=ateso-dictionary&deleted=' . count( $ids ) ) );
			exit;
		}
	}

	public function add_menu_pages() {
		add_menu_page(
			'Ateso Dictionary',
			'Dictionary',
			'manage_options',
			'ateso-dictionary',
			array( $this, 'render_list_page' ),
			'dashicons-book-alt',
			16
		);

		add_submenu_page(
			'ateso-dictionary',
			'All Entries',
			'All Entries',
			'manage_options',
			'ateso-dictionary',
			array( $this, 'render_list_page' )
		);

		add_submenu_page(
			'ateso-dictionary',
			'Add Entry',
			'Add Entry',
			'manage_options',
			'ateso-dict-add',
			array( $this, 'render_edit_page' )
		);

		add_submenu_page(
			'ateso-dictionary',
			'Import Dictionary',
			'Import',
			'manage_options',
			'ateso-dict-import',
			array( $this, 'render_import_page' )
		);
	}

	public function render_list_page() {
		$list_table = new ListTable();
		$list_table->prepare_items();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">Dictionary Entries</h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ateso-dict-add' ) ); ?>" class="page-title-action">Add New</a>
			<hr class="wp-header-end">

			<?php if ( ! empty( $_GET['deleted'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php echo esc_html( absint( $_GET['deleted'] ) ); ?> entry(ies) deleted.</p>
				</div>
			<?php endif; ?>

			<form method="get">
				<input type="hidden" name="page" value="ateso-dictionary">
				<?php
				$list_table->search_box( 'Search entries', 'dict-search' );
				$list_table->display();
				?>
			</form>
		</div>
		<?php
	}

	public function render_edit_page() {
		$edit_form = new EditForm();
		$edit_form->render();
	}

	public function render_import_page() {
		$import_page = new ImportPage();
		$import_page->render();
	}
}
