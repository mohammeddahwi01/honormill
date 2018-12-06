<?php
namespace PHXSolution\Override\Block\Widget\Grid;

class Extended
{
	public function afterGetMainButtonsHtml(\Magento\Backend\Block\Widget\Grid\Extended $subject, $result)
	{
		$result .= '<button id="check-uncheck-btn" title="Select All" type="button" class="action-default scalable action-reset action-tertiary" onclick="export_filter_gridJsObject.selectAll()" data-action="grid-filter-reset" data-ui-id="widget-button-3"><span>'.__("Check All").'</span></button>';
		return $result;
	}
}
