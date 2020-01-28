--
-- Base Table
--
CREATE TABLE `bookrequest` (
  `Bookrequest_ID` int(11) NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_date` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `bookrequest`
  ADD PRIMARY KEY (`Bookrequest_ID`);

ALTER TABLE `bookrequest`
  MODIFY `Bookrequest_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Permissions
--
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`) VALUES
('add', 'OnePlace\\Bookrequest\\Controller\\BookrequestController', 'Add', '', '', 0),
('edit', 'OnePlace\\Bookrequest\\Controller\\BookrequestController', 'Edit', '', '', 0),
('index', 'OnePlace\\Bookrequest\\Controller\\BookrequestController', 'Index', 'Bookrequests', '/bookrequest', 1),
('list', 'OnePlace\\Bookrequest\\Controller\\ApiController', 'List', '', '', 1),
('view', 'OnePlace\\Bookrequest\\Controller\\BookrequestController', 'View', '', '', 0),
('success', 'OnePlace\\Bookrequest\\Controller\\BookrequestController', 'Close as successful', '', '', 0);

--
-- Form
--
INSERT INTO `core_form` (`form_key`, `label`, `entity_class`, `entity_tbl_class`) VALUES
('bookrequest-single', 'Bookrequest', 'OnePlace\\Bookrequest\\Model\\Bookrequest', 'OnePlace\\Bookrequest\\Model\\BookrequestTable');

--
-- Index List
--
INSERT INTO `core_index_table` (`table_name`, `form`, `label`) VALUES
('bookrequest-index', 'bookrequest-single', 'Bookrequest Index');

--
-- Tabs
--
INSERT INTO `core_form_tab` (`Tab_ID`, `form`, `title`, `subtitle`, `icon`, `counter`, `sort_id`, `filter_check`, `filter_value`) VALUES ('bookrequest-base', 'bookrequest-single', 'Bookrequest', 'Base', 'fas fa-cogs', '', '0', '', '');

--
-- Buttons
--
INSERT INTO `core_form_button` (`Button_ID`, `label`, `icon`, `title`, `href`, `class`, `append`, `form`, `mode`, `filter_check`, `filter_value`) VALUES
(NULL, 'Save Bookrequest', 'fas fa-save', 'Save Bookrequest', '#', 'primary saveForm', '', 'bookrequest-single', 'link', '', ''),
(NULL, 'Edit Bookrequest', 'fas fa-edit', 'Edit Bookrequest', '/bookrequest/edit/##ID##', 'primary', '', 'bookrequest-view', 'link', '', ''),
(NULL, 'Add Bookrequest', 'fas fa-plus', 'Add Bookrequest', '/bookrequest/add', 'primary', '', 'bookrequest-index', 'link', '', '');

--
-- Fields
--
INSERT INTO `core_form_field` (`Field_ID`, `type`, `label`, `fieldkey`, `tab`, `form`, `class`, `url_view`, `url_ist`, `show_widget_left`, `allow_clear`, `readonly`, `tbl_cached_name`, `tbl_class`, `tbl_permission`) VALUES
(NULL, 'text', 'Name', 'label', 'bookrequest-base', 'bookrequest-single', 'col-md-3', '/bookrequest/view/##ID##', '', 0, 1, 0, '', '', '');

--
-- Request Criteria
--
CREATE TABLE `bookrequest_criteria` (
  `Criteria_ID` int(11) NOT NULL,
  `criteria_entity_key` varchar(100) NOT NULL,
  `label` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `compare_notice` tinyint(1) NOT NULL,
  `bookrequest_field` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `bookrequest_criteria`
  ADD PRIMARY KEY (`Criteria_ID`);


ALTER TABLE `bookrequest_criteria`
  MODIFY `Criteria_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Widget
--
INSERT INTO `core_widget` (`Widget_ID`, `widget_name`, `label`, `permission`) VALUES
(NULL, 'bookrequest_matching', 'Matching Results', 'index-Application\\Controller\\IndexController');

COMMIT;