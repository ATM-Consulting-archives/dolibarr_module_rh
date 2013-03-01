<?php

class ActionsRhmenu
{ 
     /** Overloading the doActions function : replacing the parent's function with the one below 
      *  @param      parameters  meta datas of the hook (context, etc...) 
      *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...) 
      *  @param      action             current action (if set). Generally create or edit or null 
      *  @return       void 
      */ 
    function doActions($parameters, &$object, &$action, $hookmanager) 
    {
        if (in_array('productcard',explode(':',$parameters['context']))) 
        { 
          // do something only for the context 'somecontext'
        }
 
        /*$this->results=array('myreturn'=>$myvalue);
        $this->resprints='';
 */
        return 0;
    }
    
	function formObjectOptions($parameters, &$object, &$action, $hookmanager) {
		
		if (in_array('productcard',explode(':',$parameters['context']))) 
        { 
          // do something only for the context 'somecontext'
          
         /* ?><tr>
          <td>Un champs à la mord moi le noeud</td>	
          <td><?=($action=='edit') ? 'en mode édition' : 'De bla bla bla' ?></td>	
          </tr>
          <?
          */
        }
		
	}
}