<?php
    require('config.php');
    dol_include_once('/absence/class/absence.class.php');
    dol_include_once('/absence/lib/absence.lib.php');
    dol_include_once('/valideur/class/valideur.class.php');
    dol_include_once('/user/class/usergroup.class.php');
    
    $langs->load('absence@absence');
    
    
    if($user->rights->absence->myactions->CanDeclareAbsenceForGroup!=1) exit('$user->rights->absence->myactions->CanDeclareAbsenceForGroup failed');
    
    $PDOdb=new TPDOdb;
    $absence=new TRH_Absence;
    $absence->loadTypeAbsencePerTypeUser($PDOdb);

    $absence->set_values($_POST);
    $absence->fk_group = GETPOST('fk_group');
    
    llxHeader('', $langs->trans('AbsenceRequest'));
    
    $form=new TFormCore;
    
    echo $form->begin_form($_SERVER['PHP_SELF'],'form1','POST');
    echo $form->hidden('action', 'view');
    
    _fiche($PDOdb, $absence,$form,'edit');
    
    $fk_group = GETPOST('fk_group');
    
    if(GETPOST('action') == 'view') {
        
        if(isset($_POST['create_absence'])) {
            $TUser = GETPOST('TUser');
            if(!empty($TUser)) {
                
                foreach($TUser as $fk_user) {
                    
                    $abs = new TRH_Absence;
                    
                    $abs->date_debut = $absence->date_debut;
                    $abs->ddMoment = $absence->ddMoment;
                    $abs->date_fin = $absence->date_fin;
                    $abs->dfMoment = $absence->dfMoment;
                    $abs->commentaire = $absence->commentaire;
                    $abs->type = $absence->type;
                    
                    $abs->fk_user = $fk_user;
                    $existeDeja=$abs->testExisteDeja($PDOdb, $abs);
                    
                    if($existeDeja === false) {
                        
                        $abs->calculDureeAbsenceParAddition($PDOdb);
                        
                        $abs->etat='Validee';
                        $abs->libelleEtat = $langs->trans('Accepted');
                        $abs->date_validation=time();
                        $abs->fk_user_valideur = $user->id;
                        
                        $abs->save($PDOdb, false);
                        
                        
                    }
                    
                }
                
                setEventMessage('GroupAbsCreated');
            }
            
            
        }

        _view($PDOdb, $absence, $fk_group);
        
        
    }
    echo $form->end_form();
    
    $PDOdb->close();
     
    llxFooter();
    
function _view(&$PDOdb, &$absence, $fk_group) {
    global $db;
    
    if($fk_group>0) {
        
        $group = new UserGroup($db);
        $group->fetch($fk_group);
        $TUser = $group->listUsersForGroup('',1);
       
        ?><table class="border" width="100%"><?php
       
        foreach($TUser as $fk_user_in_group) {
            
            $u=new User($db);
            $u->fetch($fk_user_in_group);
            
            $absence->fk_user = $u->id;
            $existeDeja=$absence->testExisteDeja($PDOdb, $absence);
            
            ?>
            <tr>
                <td><?php echo $u->getNomUrl(1);  ?></td>
                <td><?
                    if($existeDeja === false) {
                        echo img_picto('Ok', 'star');
                        echo '<input type="hidden" name="TUser[]" value="'.$u->id.'"/>';   
                    }
                    else{
                        
                        list($deb, $end) = $existeDeja;
                        
                        echo 'Déjà absent du '.date('d/m/Y', strtotime($deb)).' au '.date('d/m/Y', strtotime($end));
                        
                    }
                
                ?></td>
            </tr>
            <?
            
            
        }
        
        ?></table>
        <div class="tabsAction">
                    <input type="submit" class="button" name="create_absence" value="Creer et valider les absences">
        </div>
        
        <?php
        
        
        
    }
    
    
    
}  
function _fiche(&$PDOdb, &$absence, &$form, $mode) {
    global $db,$user,$conf,$langs;
    
    //echo $_REQUEST['validation'];
    
   
    
    $formDoli = new Form($db);
    
    $TBS=new TTemplateTBS();
    
    print $TBS->render('./tpl/absenceGroup.tpl.php'
        ,array(
        )
        ,array(

            'absenceCourante'=>array(
                //texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
                'id'=>$absence->getId()
                ,'commentaire'=>$form->zonetexte('','commentaire',$absence->commentaire, 30,3,'','','-')
                ,'date_debut'=> $form->doliCalendar('date_debut', $absence->date_debut) 
                ,'ddMoment'=>$form->combo('','ddMoment',$absence->TddMoment,$absence->ddMoment)
                ,'date_fin'=> $form->doliCalendar('date_fin', $absence->date_fin)
                ,'dfMoment'=>$form->combo('','dfMoment',$absence->TdfMoment,$absence->dfMoment)
                ,'idUser'=>$user->id
                ,'comboType'=>$form->combo('','type',$absence->TTypeAbsenceAdmin,$absence->type)
                ,'dt_cre'=>$absence->get_dtcre()
                ,'group'=>$formDoli->select_dolgroups($absence->fk_group, 'fk_group', 1)
                ,'titreNvDemande'=>load_fiche_titre($langs->trans('NewAbsenceRequestCollective'))
                
            )   
            ,'view'=>array(
                'mode'=>$mode
                ,'head2'=>dol_get_fiche_head(absencePrepareHead($absence, 'absenceCreation')  , 'fiche', $langs->trans('Absence'))
                ,'dateFormat'=>$langs->trans("FormatDateShortJavaInput")
                
            )
            ,'translate' => array(
                'Group' => $langs->trans('Group'),
                'showMe'=>$langs->transnoentities('Show'),
                'CurrentUser' => $langs->trans('CurrentUser'),
                'AbsenceType' => $langs->trans('AbsenceType'),
                'StartDate' => $langs->trans('StartDate'),
                'EndDate' => $langs->trans('EndDate'),
                'DurationInDays' => $langs->trans('DurationInDays'),
                'DurationInHours' => $langs->trans('DurationInHours'),
                'CountedDurationInHours' => $langs->trans('CountedDurationInHours'),
                'State' => $langs->trans('State'),
                'Warning' => $langs->trans('Warning'),
                'ValidationLevel' => $langs->trans('ValidationLevel'),
                'ValidatorComment' => $langs->trans('ValidatorComment'),
                'Comment' => $langs->trans('Comment'),
                'CreatedThe' => $langs->trans('CreatedThe'),
                'ValidatedThe' => $langs->trans('ValidatedThe'),
                'HolidaysPaid' => $langs->trans('HolidaysPaid'),
                'CumulatedDayOff' => utf8_decode($TTypeAbsence['rttcumule']),
                'NonCumulatedDayOff' => utf8_decode($TTypeAbsence['rttnoncumule']),
                'Register' => $langs->trans('Register'),
                'ConfirmAcceptAbsenceRequest' => addslashes( $langs->transnoentitiesnoconv('ConfirmAcceptAbsenceRequest') ),
                'Accept' => $langs->trans('Accept'),
                'Refuse' => $langs->trans('Refuse'),
                'ConfirmRefuseAbsenceRequest' => addslashes($langs->transnoentitiesnoconv('ConfirmRefuseAbsenceRequest')),
                'ConfirmSendToSuperiorAbsenceRequest' => addslashes($langs->transnoentitiesnoconv('ConfirmSendToSuperiorAbsenceRequest')),
                'SendToSuperiorValidator' => $langs->transnoentitiesnoconv('SendToSuperiorValidator'),
                'ConfirmDeleteAbsenceRequest' =>addslashes( $langs->transnoentitiesnoconv('ConfirmDeleteAbsenceRequest')),
                'Delete' => $langs->trans('Delete')
                ,'AbsenceBy' => $langs->trans('AbsenceBy')
                ,'acquisRecuperation'=>$langs->trans('acquisRecuperation')
            )
            
        )
    );

    
   
}

