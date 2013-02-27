<div class='header ui-widget-header'><?php $clang->eT("Edit survey settings");?></div>
<?php
    $data['clang'] = $clang;
    $data['action'] = $action;
	$yii = Yii::app();
	$controller = $yii->getController();
    $controller->render('/admin/survey/subview/tab_view',$data);
    $controller->render('/admin/survey/subview/tabGeneralEditSurvey_view',$data);
    $controller->render('/admin/survey/subview/tabPresentation_view',$data);
    $controller->render('/admin/survey/subview/tabPublication_view',$data);
    $controller->render('/admin/survey/subview/tabNotification_view',$data);
    $controller->render('/admin/survey/subview/tabTokens_view',$data);
    $controller->render('/admin/survey/subview/tabPanelIntegration_view',$data);
?>
<input type='hidden' id='surveysettingsaction' name='action' value='updatesurveysettings' />
<input type='hidden' id='sid' name='sid' value="<?php echo $esrow['sid'];?>" />
<input type='hidden' name='languageids' id='languageids' value="<?php echo $esrow['additional_languages'];?>" />
<input type='hidden' name='language' value="<?php echo $esrow['language'];?>" />
</form>
<?php
    $controller->render('/admin/survey/subview/tabResourceManagement_view',$data);
?>
</div>

<?php
    if (hasSurveyPermission($surveyid,'surveysettings','update'))
    {?>
    <p><button onclick="if (UpdateLanguageIDs(mylangs,'<?php $clang->eT("All questions, answers, etc for removed languages will be lost. Are you sure?", "js");?>')) {$('#addnewsurvey').submit();}" class='standardbtn' ><?php $clang->eT("Save"); ?></button></p>
    <p><button onclick="if (UpdateLanguageIDs(mylangs,'<?php $clang->eT("All questions, answers, etc for removed languages will be lost. Are you sure?", "js");?>')) { document.getElementById('surveysettingsaction').value = 'updatesurveysettingsandeditlocalesettings'; $('#addnewsurvey').submit();}" class='standardbtn' ><?php $clang->eT("Save & edit survey text elements");?> >></button></p><br /><?php
}?>
<div id='dlgEditParameter'>
    <div id='dlgForm' class='form30'>
        <ul>
            <li><label for='paramname'><?php $clang->eT('Parameter name:'); ?></label><input name='paramname' id='paramname' type='text' size='20' />
            </li>
            <li><label for='targetquestion'><?php $clang->eT('Target (sub-)question:'); ?></label><select name='targetquestion' id='targetquestion' size='1'>
                    <option value=''><?php $clang->eT('(No target question)'); ?></option>
                    <?php foreach ($questions as $question){?>
                        <option value='<?php echo $question['qid'].'-'.$question['sqid'];?>'><?php echo $question['title'].': '.ellipsize(flattenText($question['question'],true,true),43,.70);
                                if ($question['sqquestion']!='')
                                {
                                    echo ' - '.ellipsize(flattenText($question['sqquestion'],true,true),30,.75);
                                }
                        ?></option> <?php
                    }?>
                </select>
            </li>
        </ul>
    </div>
    <p><button id='btnSaveParams'><?php $clang->eT('Save'); ?></button> <button id='btnCancelParams'><?php $clang->eT('Cancel'); ?></button> </p>
</div>
