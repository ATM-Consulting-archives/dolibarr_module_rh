<?php $this->render("/installer/header_view", compact('progressValue', 'clang')); ?>

<div class="container_6">

	<?php $this->render('/installer/sidebar_view', compact('progressValue', 'classesForStep', 'clang')); ?>

    <div class="grid_4 table">

        <p class="maintitle">&nbsp;<?php echo $title; ?></p>

        <div style="-moz-border-radius:15px; border-radius:15px;" >
            <p>&nbsp;<?php echo $descp; ?></p>
            <hr />
            <br />
            <div class='messagebox'><div class='header'><?php $clang->eT('LimeSurvey setup'); ?></div>
                <?php if (isset($adminoutputText)) echo $adminoutputText; ?>
            </div><br />
        </div>
    </div>
</div>
<div class="clear"></div>
<div class="container_6">
    <div class="grid_2">&nbsp;</div>
    <div class="grid_4 demo">
        <br/>
        <table style="font-size:11px; width: 694px;">
            <tbody>
                <tr>
                    <td align="left" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="<?php $clang->eT('Previous'); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/database"); ?>', '_top')" /></td>
                    <td align="center" style="width: 227px;"></td>
                    <td align="right" style="width: 227px;"><?php if (isset($adminoutputForm)) echo $adminoutputForm; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php $this->render("/installer/footer_view"); ?>