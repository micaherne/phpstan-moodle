<?php 
global $CFG;
require_once $CFG->dirroot . "/question/bank/importquestions/classes/form/import_form.php";
require_once $CFG->dirroot . "/lib/adminlib.php";
class_alias(core_courseformat\base::class, 'format_base');
class_alias(format_topics\output\renderer::class, 'format_topics_renderer');
class_alias(core_courseformat\output\section_renderer::class, 'format_section_renderer_base');
class_alias(format_singleactivity\output\renderer::class, 'format_singleactivity_renderer');
class_alias(core_courseformat\output\site_renderer::class, 'format_site_renderer');
class_alias(format_weeks\output\renderer::class, 'format_weeks_renderer');
class_alias(core_question\local\bank\action_column_base::class, 'core_question\bank\action_column_base');
class_alias(core_question\local\bank\checkbox_column::class, 'core_question\bank\checkbox_column');
class_alias(core_question\local\bank\column_base::class, 'core_question\bank\column_base');
class_alias(core_question\local\bank\edit_menu_column::class, 'core_question\bank\edit_menu_column');
class_alias(core_question\local\bank\menu_action_column_base::class, 'core_question\bank\menu_action_column_base');
class_alias(core_question\local\bank\menuable_action::class, 'core_question\bank\menuable_action');
class_alias(core_question\local\bank\random_question_loader::class, 'core_question\bank\random_question_loader');
class_alias(core_question\local\bank\row_base::class, 'core_question\bank\row_base');
class_alias(core_question\local\bank\view::class, 'core_question\bank\view');
class_alias(qbank_viewcreator\creator_name_column::class, 'core_question\bank\creator_name_column');
class_alias(qbank_viewquestionname\viewquestionname_column_helper::class, 'core_question\bank\question_name_column');
class_alias(qbank_viewquestionname\question_name_idnumber_tags_column::class, 'core_question\bank\question_name_idnumber_tags_column');
class_alias(qbank_viewquestiontext\question_text_row::class, 'core_question\bank\question_text_row');
class_alias(qbank_viewquestiontype\question_type_column::class, 'core_question\bank\question_type_column');
class_alias(qbank_editquestion\qbank_chooser::class, 'core_question\output\qbank_chooser');
class_alias(qbank_editquestion\qbank_chooser_item::class, 'core_question\output\qbank_chooser_item');
class_alias(qbank_managecategories\form\question_move_form::class, 'question_move_form');
class_alias(qbank_importquestions\form\question_import_form::class, 'question_import_form');
class_alias(qbank_managecategories\question_category_list::class, 'question_category_list');
class_alias(qbank_managecategories\question_category_list_item::class, 'question_category_list_item');
class_alias(qbank_managecategories\question_category_object::class, 'question_category_object');
class_alias(qbank_exportquestions\form\export_form::class, 'export_form');
class_alias(qbank_previewquestion\form\preview_options_form::class, 'preview_options_form');
class_alias(qbank_tagquestion\form\tags_form::class, 'core_question\form\tags');
class_alias(core_question\local\bank\context_to_string_translator::class, 'context_to_string_translator');
class_alias(core_question\local\bank\question_edit_contexts::class, 'question_edit_contexts');
class_alias(core_admin\reportbuilder\local\systemreports\task_logs::class, 'core_admin\local\systemreports\task_logs');
class_alias(core_admin\reportbuilder\local\entities\task_log::class, 'core_admin\local\entities\task_log');
class_alias(core_course\reportbuilder\local\entities\course_category::class, 'core_course\local\entities\course_category');
class_alias(core_cohort\reportbuilder\local\entities\cohort::class, 'core_cohort\local\entities\cohort');
class_alias(core_cohort\reportbuilder\local\entities\cohort_member::class, 'core_cohort\local\entities\cohort_member');
class_alias(core_block\navigation\views\secondary::class, 'core_block\local\views\secondary');
class_alias(core_question\local\bank\condition::class, 'core_question\bank\search\condition');
class_alias(qbank_managecategories\category_condition::class, 'core_question\bank\search\category_condition');
class_alias(qbank_deletequestion\hidden_condition::class, 'core_question\bank\search\hidden_condition');
class_alias(mod_assign\output\assign_header::class, 'assign_header');
class_alias(mod_assign\output\assign_submission_status::class, 'assign_submission_status');
class_alias(mod_assign\navigation\views\secondary::class, 'mod_assign\local\views\secondary');
class_alias(mod_quiz\navigation\views\secondary::class, 'mod_quiz\local\views\secondary');
class_alias(mod_quiz\question\display_options::class, 'mod_quiz_display_options');
class_alias(mod_quiz\question\qubaids_for_quiz::class, 'qubaids_for_quiz');
class_alias(mod_quiz\question\qubaids_for_quiz_user::class, 'qubaids_for_quiz_user');
class_alias(mod_quiz\admin\browser_security_setting::class, 'mod_quiz_admin_setting_browsersecurity');
class_alias(mod_quiz\admin\grade_method_setting::class, 'mod_quiz_admin_setting_grademethod');
class_alias(mod_quiz\admin\overdue_handling_setting::class, 'mod_quiz_admin_setting_overduehandling');
class_alias(mod_quiz\admin\review_setting::class, 'mod_quiz_admin_review_setting');
class_alias(mod_quiz\admin\user_image_setting::class, 'mod_quiz_admin_setting_user_image');
class_alias(mod_quiz\adminpresets\adminpresets_browser_security_setting::class, 'mod_quiz\adminpresets\adminpresets_mod_quiz_admin_setting_browsersecurity');
class_alias(mod_quiz\adminpresets\adminpresets_grade_method_setting::class, 'mod_quiz\adminpresets/adminpresets_mod_quiz_admin_setting_grademethod');
class_alias(mod_quiz\adminpresets\adminpresets_overdue_handling_setting::class, 'mod_quiz\adminpresets\adminpresets_mod_quiz_admin_setting_overduehandling');
class_alias(mod_quiz\adminpresets\adminpresets_review_setting::class, 'mod_quiz\adminpresets\adminpresets_mod_quiz_admin_review_setting');
class_alias(mod_quiz\adminpresets\adminpresets_user_image_setting::class, 'mod_quiz\adminpresets\adminpresets_mod_quiz_admin_setting_user_image');
class_alias(mod_quiz\local\reports\report_base::class, 'quiz_default_report');
class_alias(mod_quiz\local\reports\attempts_report::class, 'quiz_attempts_report');
class_alias(mod_quiz\local\reports\attempts_report_options_form::class, 'mod_quiz_attempts_report_form');
class_alias(mod_quiz\local\reports\attempts_report_options::class, 'mod_quiz_attempts_report_options');
class_alias(mod_quiz\local\reports\attempts_report_table::class, 'quiz_attempts_report_table');
class_alias(mod_quiz\access_manager::class, 'quiz_access_manager');
class_alias(mod_quiz\form\preflight_check_form::class, 'mod_quiz_preflight_check_form');
class_alias(mod_quiz\form\edit_override_form::class, 'quiz_override_form');
class_alias(mod_quiz\local\access_rule_base::class, 'quiz_access_rule_base');
class_alias(mod_quiz\form\add_random_form::class, 'quiz_add_random_form');
class_alias(mod_quiz\output\links_to_other_attempts::class, 'mod_quiz_links_to_other_attempts');
class_alias(mod_quiz\output\view_page::class, 'mod_quiz_view_object');
class_alias(mod_quiz\output\renderer::class, 'mod_quiz_renderer');
class_alias(mod_quiz\output\navigation_question_button::class, 'quiz_nav_question_button');
class_alias(mod_quiz\output\navigation_section_heading::class, 'quiz_nav_section_heading');
class_alias(mod_quiz\output\navigation_panel_base::class, 'quiz_nav_panel_base');
class_alias(mod_quiz\output\navigation_panel_attempt::class, 'quiz_attempt_nav_panel');
class_alias(mod_quiz\output\navigation_panel_review::class, 'quiz_review_nav_panel');
class_alias(mod_quiz\quiz_attempt::class, 'quiz_attempt');
class_alias(mod_quiz\quiz_settings::class, 'quiz');
class_alias(report_configlog\reportbuilder\local\systemreports\config_changes::class, 'report_configlog\local\systemreports\config_changes');
class_alias(report_configlog\reportbuilder\local\entities\config_change::class, 'report_configlog\local\entities\config_change');
class_alias(quizaccess_seb\seb_quiz_settings::class, 'quizaccess_seb\quiz_settings');
class_alias(quizaccess_seb\seb_access_manager::class, 'quizaccess_seb\access_manager');
