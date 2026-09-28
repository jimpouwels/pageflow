<?php

namespace Pageflow\Core\modules\blocks\visuals\blocks;

use Pageflow\Core\database\dao\BlockDao;
use Pageflow\Core\database\dao\BlockDaoMysql;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\view\TemplateData;
use Pageflow\Core\view\views\Panel;
use Pageflow\Core\view\views\Pulldown;
use Pageflow\Core\view\views\SingleCheckbox;
use Pageflow\Core\view\views\TemplatePicker;
use Pageflow\Core\view\views\TextField;
use const Pageflow\Core\ACTION_FORM_ID;
use const Pageflow\core\ADD_ELEMENT_FORM_ID;
use const Pageflow\Core\DELETE_ELEMENT_FORM_ID;
use const Pageflow\core\EDIT_ELEMENT_HOLDER_ID;
use const Pageflow\Core\ELEMENT_HOLDER_FORM_ID;

class BlockMetadataEditor extends Panel {

    private Block $currentBlock;
    private BlockDao $blockDao;

    public function __construct(Block $current) {
        parent::__construct('Algemeen', 'block_meta');
        $this->currentBlock = $current;
        $this->blockDao = BlockDaoMysql::getInstance();
    }

    public function getPanelContentTemplate(): string {
        return "blocks/templates/blocks/metadata.tpl";
    }

    public function loadPanelContent(TemplateData $data): void {
        $nameField = new TextField("name", $this->getTextResource('blocks_edit_metadata_name_field_label'), $this->currentBlock->getName(), true, false, null);
        $titleField = new TextField("title", $this->getTextResource("blocks_edit_metadata_title_field_label"), $this->currentBlock->getTitle(), true, false, null);
        $publishedField = new SingleCheckbox("published", $this->getTextResource("blocks_edit_metadata_ispublished_field_label"), $this->currentBlock->isPublished(), false, null);
        $templatePickerField = new TemplatePicker("block_template", $this->getTextResource("blocks_edit_metadata_template_field_label"), $this->currentBlock->getTemplateId(), false, "", $this->currentBlock->getScope());

        $this->assignElementHolderFormIds($data);
        $data->assign("current_block_id", $this->currentBlock->getId());
        $data->assign("element_holder_version", $this->currentBlock->getVersion());
        $data->assign("name_field", $nameField->render());
        $data->assign("title_field", $titleField->render());
        $data->assign("published_field", $publishedField->render());
        $data->assign("template_picker_field", $templatePickerField->render());
        $data->assign("positions_field", $this->renderPositionsField());
    }

    private function renderPositionsField(): string {
        $positionsOptions = array();
        foreach ($this->blockDao->getBlockPositions() as $position) {
            $positionsOptions[] = array("name" => $position->getName(), "value" => $position->getId());
        }
        $currentPosition = null;
        if (!is_null($this->currentBlock->getPosition())) {
            $currentPosition = $this->currentBlock->getPosition()->getId();
        }
        $positionsField = new Pulldown("block_position", $this->getTextResource("blocks_edit_metadata_position_field_label"), $currentPosition, $positionsOptions, false, null, true);
        return $positionsField->render();
    }

    private function assignElementHolderFormIds($data): void {
        $data->assign("add_element_form_id", ADD_ELEMENT_FORM_ID);
        $data->assign("edit_element_holder_id", EDIT_ELEMENT_HOLDER_ID);
        $data->assign("element_holder_form_id", ELEMENT_HOLDER_FORM_ID);
        $data->assign("action_form_id", ACTION_FORM_ID);
        $data->assign("delete_element_form_id", DELETE_ELEMENT_FORM_ID);
    }

}
