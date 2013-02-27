<?php
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *	$Id$
 */
/**
 * This is the model class for table "{{{{participant_attribute_names}}}}".
 *
 * The followings are the available columns in table '{{{{participant_attribute_names}}}}':
 * @property integer $attribute_id
 * @property string $attribute_type
 * @property string $visible
 */
class ParticipantAttributeNames extends CActiveRecord
{
    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey() {
        return 'attribute_id';
    }

    /**
     * Returns the static model of ParticipantAttributeNames table
     *
     * @static
     * @access public
     * @param string $class
     * @return ParticipantAttributeNames
     */
    public static function model($class = __CLASS__) {
        $model = parent::model($class);
        $keys = $model->tableSchema->primaryKey;
        if (is_array($keys) && count($keys)== 2) {
            // Fix the primary key, needed for PgSQL http://bugs.limesurvey.org/view.php?id=6707
            // First load the helper
            Yii::app()->loadHelper('update/updatedb');
            $dbType = setsDBDriverName();
            setVarchar($dbType);
            $table = 'participant_attribute_names';
            if ($dbType == 'mysql') {
                // Only for mysql first remove auto increment
                alterColumn($model->tableName(), $model->primaryKey(), $model->tableSchema->getColumn($model->primaryKey())->dbType, false);
            }
            dropPrimaryKey($table);
            addPrimaryKey($table, (array) $model->primaryKey());
            if ($dbType == 'mysql') {
                // Add back auto increment
                alterColumn($model->tableName(), $model->primaryKey(), Yii::app()->getConfig('autoincrement'));
            }
            // Refresh all schema data now just to make sure
            Yii::app()->db->schema->refresh();
            $model->refreshMetaData();
        }
        return $model;
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return '{{participant_attribute_names}}';
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('attribute_type, visible', 'required'),
			array('attribute_type', 'length', 'max'=>4),
			array('visible', 'length', 'max'=>5),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('attribute_id, attribute_type, visible', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
            'participant_attribute_names_lang'=>array(self::HAS_MANY, 'ParticipantAttributeNamesLang', 'attribute_id'),
            'participant_attribute'=>array(self::HAS_ONE, 'Participant_attribute', 'attribute_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'attribute_id' => 'Attribute',
			'attribute_type' => 'Attribute Type',
			'visible' => 'Visible',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('attribute_id',$this->attribute_id);
		$criteria->compare('attribute_type',$this->attribute_type,true);
		$criteria->compare('visible',$this->visible,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    function getAllAttributes()
    {
        return Yii::app()->db->createCommand()->select('{{participant_attribute_names}}.*,{{participant_attribute_names}}_lang.*')
                                              ->from('{{participant_attribute_names}}')
                                              ->order('{{participant_attribute_names}}.attribute_id', 'desc')
                                              ->join('{{participant_attribute_names}}_lang', '{{participant_attribute_names}}_lang.attribute_id = {{participant_attribute_names}}.attribute_id')
                                              ->where("{{participant_attribute_names}}_lang.lang = '".Yii::app()->session['adminlang']."'")
                                              ->queryAll();
    }

    function getAllAttributesValues()
    {
        return Yii::app()->db->createCommand()->select('*')->from('{{participant_attribute_values}}')->queryAll();
    }

    function getVisibleAttributes()
    {
        $currentlang=Yii::app()->session['adminlang'];
        $output=array();
        //First get all the distinct id's that are visible
        $ids = ParticipantAttributeNames::model()->findAll("visible = 'TRUE'");
        //Then find a language for each one - the current $lang, if possible, english second, otherwise, the first in the list
        foreach($ids as $id) {
            $langs=ParticipantAttributeNamesLang::model()->findAll("attribute_id = :attribute_id", array(":attribute_id"=>$id->attribute_id));
            $language=null;
            foreach($langs as $lang) {
                //If we can find a language match, set the language and exit
                if($lang->lang == $currentlang) {
                    $language = $lang->lang;
                    $attribute_name = $lang->attribute_name;
                    break;
                }
                if($lang->lang == "en") {
                    $language = $lang->lang;
                    $attribute_name = $lang->attribute_name;
                }
            }
            if($language==null) {
                $language=$langs[0]->lang;
                $attribute_name=$langs[0]->attribute_name;
            }
            $output[]=array("attribute_id"=>$id->attribute_id,
                          "attribute_type"=>$id->attribute_type,
                          "visible"=>$id->visible,
                          "attribute_name"=>$attribute_name,
                          "lang"=>$language);
        }
        return $output;
    }

    /**
    * Returns a list of attributes, with name and value. Currently not working for alternate languages
    *
    * @param mixed $participant_id the id of the participant to return values/names for (if empty, returns all)
    */
    function getParticipantVisibleAttribute($participant_id)
    {
        $output=array();

        if($participant_id != ''){
            $findCriteria=new CDbCriteria();
            $findCriteria->addCondition('participant_id = :participant_id');
            $findCriteria->params = array(':participant_id'=>$participant_id);
            $records=ParticipantAttributeNames::model()->with('participant_attribute_names_lang', 'participant_attribute')
                                                       ->findAll($findCriteria);
            foreach($records as $row) { //Iterate through each attribute
                $thisname="";
                $thislang="";
                foreach($row->participant_attribute_names_lang as $names) { //Iterate through each language version of this attribute
                    if($thisname=="") {$thisname=$names->attribute_name; $thislang=$names->lang;} //Choose the first item by default
                    if($names->lang == Yii::app()->session['adminlang']) {$thisname=$names->attribute_name; $thislang=$names->lang;} //Override the default with the admin language version if found
                }
                $output[]=array('participant_id'=>$row->participant_attribute->participant_id,
                                'attribute_id'=>$row->attribute_id,
                                'attribute_type'=>$row->attribute_type,
                                'attribute_display'=>$row->visible,
                                'attribute_name'=>$thisname,
                                'value'=>$row->participant_attribute->value,
                                'lang'=>$thislang);
            }
            return $output;

        } else {
            $findCriteria=new CDbCriteria();
            $records=ParticipantAttributeNames::model()->with('participant_attribute_names_lang', 'participant_attribute')->findAll($findCriteria);
            foreach($records as $row) { //Iterate through each attribute
                $thisname="";
                $thislang="";
                foreach($row->participant_attribute_names_lang as $names) { //Iterate through each language version of this attribute
                    if($thisname=="") {$thisname=$names->attribute_name; $thislang=$names->lang;} //Choose the first item by default
                    if($names->lang == Yii::app()->session['adminlang']) {$thisname=$names->attribute_name; $thislang=$names->lang;} //Override the default with the admin language version if found
                }
                $output[]=array('participant_id'=>$row->participant_attribute->participant_id,
                                'attribute_id'=>$row->attribute_id,
                                'attribute_type'=>$row->attribute_type,
                                'attribute_display'=>$row->visible,
                                'attribute_name'=>$thisname,
                                'value'=>$row->participant_attribute->value,
                                'lang'=>$thislang);
            }
            return $output;
        }
    }

    function getAttributeValue($participantid,$attributeid)
    {
        $data = Yii::app()->db->createCommand()
                              ->select('*')
                              ->from('{{participant_attribute}}')
                              ->where('participant_id = :participant_id AND attribute_id = :attribute_id')
                              ->bindValues(array(':participant_id'=>$participantid, ':attribute_id'=>$attributeid))
                              ->queryRow();
        return $data;
    }

    function getAttributes($count = false, $limit = -1, $offset = -1)
    {
        $findCriteria=new CDbCriteria();
        $findCriteria->offset=$offset;
        $findCriteria->limit=$limit;
        $output=array();
        $records = ParticipantAttributeNames::model()->with('participant_attribute_names_lang')->findAll($findCriteria);
        foreach($records as $row) { //Iterate through each attribute
            $thisname="";
            $thislang="";
            foreach($row->participant_attribute_names_lang as $names) { //Iterate through each language version of this attribute
                if($thisname=="") {$thisname=$names->attribute_name; $thislang=$names->lang;} //Choose the first item by default
                if($names->lang == Yii::app()->session['adminlang']) {$thisname=$names->attribute_name; $thislang=$names->lang;} //Override the default with the admin language version if found
            }
            $output[]=array('attribute_id'=>$row->attribute_id,
                            'attribute_type'=>$row->attribute_type,
                            'attribute_display'=>$row->visible,
                            'attribute_name'=>$thisname,
                            'lang'=>$thislang);
        }

        /* $command = Yii::app()->db->createCommand()
                                 ->from('{{participant_attribute_names}}')
                                 ->leftjoin('{{participant_attribute_names_lang}}', '{{participant_attribute_names}}.attribute_id = {{participant_attribute_names_lang}}.attribute_id')
                                 ->where('lang = "'.Yii::app()->session['adminlang'].'"')
                                 ->limit(intval($limit), intval($offset)); */
        if (empty($count))
        {
            return $output;
        }
        else
        {
            return count($output);
        }
    }

    function getAttributesValues($attribute_id)
    {
       return Yii::app()->db->createCommand()
                            ->select('*')
                            ->from('{{participant_attribute_values}}')
                            ->where('attribute_id = :attribute_id')
                            ->bindParam(":attribute_id", $attribute_id, PDO::PARAM_INT)
                            ->queryAll();
    }

    // this is a very specific function used to get the attributes that are not present for the participant
    function getnotaddedAttributes($attributeid)
    {
        $output = array();
        $notin=array();
    	foreach($attributeid as $row)
    	{
    		$notin[] = $row;
    	}

        $criteria = new CDbCriteria();
        $criteria->addNotInCondition('t.attribute_id', $attributeid);
        $records = ParticipantAttributeNames::model()->with('participant_attribute_names_lang')->findAll($criteria);
        foreach($records as $row) { //Iterate through each attribute
            $thisname="";
            $thislang="";
            foreach($row->participant_attribute_names_lang as $names) { //Iterate through each language version of this attribute
                if($thisname=="") {$thisname=$names->attribute_name; $thislang=$names->lang;} //Choose the first item by default
                if($names->lang == Yii::app()->session['adminlang']) {$thisname=$names->attribute_name; $thislang=$names->lang;} //Override the default with the admin language version if found
            }
            $output[]=array('attribute_id'=>$row->attribute_id,
                            'attribute_type'=>$row->attribute_type,
                            'attribute_display'=>$row->visible,
                            'attribute_name'=>$thisname,
                            'lang'=>$thislang);
        }
        return $output;

    }

    function storeAttribute($data)
    {
        $insertnames = array('attribute_type' => $data['attribute_type'],
                            'visible' => $data['visible']);
        Yii::app()->db->createCommand()
                  ->insert('{{participant_attribute_names}}',$insertnames);
        $attribute_id = getLastInsertID($this->tableName());
        $insertnameslang = array('attribute_id' => intval($attribute_id),
                                 'attribute_name'=> $data['attribute_name'],
                                 'lang' => Yii::app()->session['adminlang']);
        Yii::app()->db->createCommand()
                  ->insert('{{participant_attribute_names_lang}}',$insertnameslang);

        return $attribute_id;

    }

    function editParticipantAttributeValue($data)
    {
        $query = Participant_attribute::model()->find('participant_id = :participant_id AND attribute_id=:attribute_id',
                                                      array(':participant_id'=>$data['participant_id'],
                                                            ':attribute_id'=>$data['attribute_id'])
                                                      );
        if(count($query) == 0)
	    {
            Yii::app()->db->createCommand()
                      ->insert('{{participant_attribute}}',$data);
	    }
	    else
	    {
            Yii::app()->db->createCommand()
                      ->update('{{participant_attribute}}',
                               $data,
                               'participant_id = :participant_id AND attribute_id = :attribute_id',
                               array(':participant_id' => $data['participant_id'], ':attribute_id'=>$data['attribute_id']));
		}

    }

    function delAttribute($attid)
    {
        Yii::app()->db->createCommand()->delete('{{participant_attribute_names_lang}}', 'attribute_id = '.$attid);
        Yii::app()->db->createCommand()->delete('{{participant_attribute_names}}', 'attribute_id = '.$attid);
        Yii::app()->db->createCommand()->delete('{{participant_attribute_values}}', 'attribute_id = '.$attid);
        Yii::app()->db->createCommand()->delete('{{participant_attribute}}', 'attribute_id = '.$attid);
    }

    function delAttributeValues($attid,$valid)
    {
        Yii::app()->db
                  ->createCommand()
                  ->delete('{{participant_attribute_values}}', 'attribute_id = '.$attid.' AND value_id = '.$valid);
    }

    function getAttributeNames($attributeid)
    {
        return Yii::app()->db->createCommand()->where("attribute_id = :attribute_id")->from('{{participant_attribute_names_lang}}')->select('*')->bindParam(":attribute_id", $attributeid, PDO::PARAM_INT)->queryAll();
    }

    function getAttributeName($attributeid, $lang='en')
    {
        return Yii::app()->db->createCommand()->where("attribute_id = :attribute_id AND lang = :lang")->from('{{participant_attribute_names_lang}}')->select('*')->bindParam(":attribute_id", $attributeid, PDO::PARAM_INT)->bindParam(":lang", $lang, PDO::PARAM_STR)->queryRow();
    }

    function getAttribute($attribute_id)
    {
        $data = Yii::app()->db->createCommand()->from('{{participant_attribute_names}}')->where('{{participant_attribute_names}}.attribute_id = '.$attribute_id)->select('*')->queryRow();
        return $data;
    }

    function saveAttribute($data)
    {
        if (empty($data['attribute_id']))
        {
            return;
        }

        $insertnames = array();
        if (!empty($data['attribute_type']))
        {
            $insertnames['attribute_type'] = $data['attribute_type'];
        }
        if (!empty($data['visible']))
        {
            $insertnames['visible'] = $data['visible'];
        }
        if (!empty($insertnames))
        {
            self::model()->updateAll($insertnames, 'attribute_id = :id', array(':id' => $data['attribute_id']));
        }

        if (!empty($data['attribute_name']))
        {
            Yii::app()->db->createCommand()
                    ->update('{{participant_attribute_names_lang}}', array('attribute_name' => $data['attribute_name']),
                                'attribute_id = :attribute_id AND lang=:lang', array(
                                        ':lang' => Yii::app()->session['adminlang'],
                                        ':attribute_id' => $data['attribute_id'],
                                    ));
        }
    }

    function saveAttributeLanguages($data)
    {
        $query = Yii::app()->db->createCommand()->from('{{participant_attribute_names_lang}}')->where('attribute_id = :attribute_id AND lang = :lang')->select('*')->bindParam(":attribute_id", $data['attribute_id'], PDO::PARAM_INT)->bindParam(":lang", $data['lang'], PDO::PARAM_STR)->queryAll();
        if (count($query) == 0)
        {
              // A record does not exist, insert one.
               $record = array('attribute_id'=>$data['attribute_id'],'attribute_name'=>$data['attribute_name'],'lang'=>$data['lang']);
               $query = Yii::app()->db->createCommand()->insert('{{participant_attribute_names_lang}}', $data);
        }
        else
        {
             // A record does exist, update it.
            $query = Yii::app()->db->createCommand()
                ->update('{{participant_attribute_names_lang}}', array('attribute_name' => $data['attribute_name']),
                            'attribute_id = :attribute_id  AND lang= :lang', array(
                                    ':attribute_id' => $data['attribute_id'],
                                    ':lang' => $data['lang'],
                                ));
        }
    }

    function storeAttributeValues($data)
    {
        foreach ($data as $record) {
    		Yii::app()->db->createCommand()->insert('{{participant_attribute_values}}',$record);
    	}
    }

    function storeAttributeCSV($data)
    {
        $insertnames = array('attribute_type' => $data['attribute_type'],
                            'visible' => $data['visible']);
		Yii::app()->db->createCommand()->insert('{{participant_attribute_names}}', $insertnames);

        $insertid = getLastInsertID($this->tableName());
        $insertnameslang = array('attribute_id' => $insertid,
                                 'attribute_name'=>$data['attribute_name'],
                                 'lang' => Yii::app()->session['adminlang']);
		Yii::app()->db->createCommand()->insert('{{participant_attribute_names_lang}}', $insertnameslang);
        return $insertid;
    }

    //updates the attribute values in participant_attribute_values
    function saveAttributeValue($data)
    {
        Yii::app()->db->createCommand()
                  ->update('{{participant_attribute_values}}', $data, "attribute_id = :attribute_id AND value_id = :value_id", array(":attribute_id" => $data['attribute_id'], ":value_id" => $data['value_id']));
                  //->bindParam(":attribute_id", $data['attribute_id'], PDO::PARAM_INT)->bindParam(":value_id", $data['value_id'], PDO::PARAM_INT);
    }

    function saveAttributeVisible($attid,$visiblecondition)
    {

        $attribute_id = explode("_", $attid);
        $data=array('visible'=>$visiblecondition);
        if($visiblecondition == "")
        {
            $data=array('visible'=>'FALSE');
        }
        Yii::app()->db->createCommand()->update('{{participant_attribute_names}}',$data,'attribute_id = :attribute_id')->bindParam(":attribute_id", $attribute_id[1], PDO::PARAM_INT);
    }

    function getAttributeID()
    {
		$query = Yii::app()->db->createCommand()->select('attribute_id')->from('{{participant_attribute_names}}')->order('attribute_id','desc')->queryAll();
        return $query;
    }


    function saveParticipantAttributeValue($data)
    {
    	Yii::app()->db->createCommand()->insert('{{participant_attribute}}', $data);
    }
}
