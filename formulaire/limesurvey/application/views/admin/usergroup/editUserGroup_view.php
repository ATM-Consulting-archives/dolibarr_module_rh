<div class='header ui-widget-header'><?php echo sprintf($clang->gT("Editing user group (Owner: %s)"), Yii::app()->session['user']); ?></div>
    <?php echo CHtml::form(array("admin/usergroups/sa/edit/ugid/{$ugid}"), 'post', array('class'=>'form30', 'id'=>'usergroupform', 'name'=>'usergroupform')); ?>
        <ul>
        <li><label for='name'><?php $clang->eT("Name:"); ?></label>
        <input type='text' size='50' maxlength='20' id='name' name='name' value="<?php echo $esrow['name']; ?>" /></li>
        <li><label for='description'><?php $clang->eT("Description:"); ?></label>
        <textarea cols='50' rows='4' id='description' name='description'><?php echo $esrow['description']; ?></textarea></li>
        <ul><p><input type='submit' value='<?php $clang->eT("Update user group"); ?>' />
        <input type='hidden' name='action' value='editusergroupindb' />
        <input type='hidden' name='owner_id' value='<?php echo Yii::app()->session['loginID']; ?>' />
        <input type='hidden' name='ugid' value='<?php echo $ugid; ?>' />
    </form>