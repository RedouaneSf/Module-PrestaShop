<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class newfield extends Module
{
    private $translator;
    private $language;

    private $ask_for_birthdate = true;
    private $ask_for_partner_optin = true;
    private $partner_optin_is_required = true;
    private $ask_for_password = true;
    private $password_is_required = true;
    private $ask_for_new_password = false;
    
    public function __construct()
    {
        $this->name = 'newfield';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Redouane sfarjli';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('newfield Module Name');
        $this->description = $this->l('Description newfield');

        $this->ps_versions_compliancy = array('min' => '1.7.8.0', 'max' => _PS_VERSION_);
    }

    public function install()
{
    return parent::install() 
     && $this->registerHook('additionalCustomerFormFields')&& $this->registerHook('actionObjectCustomerUpdateAfter');
    
}

 

function _renderList()
   {
       $sql = 'SELECT * FROM '._DB_PREFIX_.'customer';

       if ($result = Db::getInstance()->executeS($sql))
       {
             
           //Les champs à afficher
           $fields_list = array(
               'id_customer' => array(
                   'title' => $this->l('Id'),
                   'width' => 140,
                   'type' => 'text',
               ),
               'firstname' => array(
                   'title' => $this->l('FirstName'),
                   'width' => 140,
                   'type' => 'text',
               ),
               'lastname' => array(
                'title' => $this->l('lastname'),
                'width' => 140,
                'type' => 'text',
            ),
            'email' => array(
                'title' => $this->l('email'),
                'width' => 140,
                'type' => 'text',
            ),
            'id_default_group' => array(
                'title' => $this->l('id_default_group'),
                'width' => 140,
                'type' => 'text',
                
            ),
            'siret' => array(
                'title' => $this->l('siret'),
                'width' => 140,
                'type' => 'text',
            ),
            'company' => array(
                'title' => $this->l('company'),
                'width' => 140,
                'type' => 'text',
            ),
               
           );
           $groups = Group::getGroups($this->context->language->id);
            $options = array();

            foreach ($groups as $group) {
                $options[] = array(
                    'id_option' => $group['id_group'],
                    'name' => $group['name']
                );
           }

           $helper = new HelperList();
           $helper->shopLinkType = '';
           $helper->simple_header = false;
           $helper->actions = array('edit');
           $helper->identifier = 'id_customer';
           $helper->show_toolbar = true;
           $helper->title = 'Listing client';
           $helper->table = 'customer';
           $helper->token = Tools::getAdminTokenLite('AdminModules');
           $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
           

           return $helper->generateList($result, $fields_list);
       }

       return false;
   }

    public function uninstall()
    {
        return parent::uninstall();
    }

 
  

    public function hookAdditionalCustomerFormFields($params)
    {
       $this->context->controller->addJS($this->_path . 'js/monmodule.js');
       $this->context->controller->addCSS($this->_path.'css/monmodule.css');
        
        $format = [];
        $genders = Gender::getGenders();
        if ($genders->count() > 0) {
            $genderField = (new FormField())
                ->setName('id_gender')
                ->setType('radio-buttons')
                ->setLabel(
                    $this->trans(
                        'Social title',
                        [],
                        'Shop.Forms.Labels'
                    )
                );
            foreach ($genders as $gender) {
                $genderField->addAvailableValue($gender->id, $gender->name);
            }
            $format[$genderField->getName()] = $genderField;
        }
       
        $format['firstname'] = (new FormField())
            ->setName('firstname')
            ->setLabel(
                $this->trans(
                    'First name',
                    [],
                    'Shop.Forms.Labels'
                )
            )
            ->setRequired(true)
            ->addAvailableValue(
                'comment',
                $this->trans('Only letters and the dot (.) character, followed by a space, are allowed.', [], 'Shop.Forms.Help')
            );
            $format['lastname'] = (new FormField())
            ->setName('lastname')
            ->setLabel(
                $this->trans(
                    'Last name',
                    [],
                    'Shop.Forms.Labels'
                )
            )
            ->setRequired(true)
            ->addAvailableValue(
                'comment',
                $this->trans('Only letters and the dot (.) character, followed by a space, are allowed.', [], 'Shop.Forms.Help')
            );
            $format['email'] = (new FormField())
            ->setName('email')
            ->setType('email')
            ->setLabel(
                $this->trans(
                    'Email',
                    [],
                    'Shop.Forms.Labels'
                )
            )
            ->setRequired(true);
            if ($this->ask_for_password) {
                $format['password'] = (new FormField())
                    ->setName('password')
                    ->setType('password')
                    ->setLabel(
                        $this->trans(
                            'Password',
                            [],
                            'Shop.Forms.Labels'
                        )
                    )
                    ->setRequired($this->password_is_required);
            }

            
            if ($this->ask_for_birthdate) {
                $format['birthday'] = (new FormField())
                    ->setName('birthday')
                    ->setType('text')
                    ->setLabel(
                        $this->trans(
                            'Birthdate',
                            [],
                            'Shop.Forms.Labels'
                        )
                    )
                    ->addAvailableValue('placeholder', Tools::getDateFormat())
                    ->addAvailableValue(
                        'comment',
                        $this->trans('(E.g.: %date_format%)', ['%date_format%' => Tools::formatDateStr('31 May 1970')], 'Shop.Forms.Help')
                    );
            }
     
            
 
            $format['id_default_group']=  (new FormField())
            ->setName('id_default_group')
            ->setType('radio-buttons')
            ->setAvailableValues(array('3' => 'Particulier', '4' => 'Professionnel'))
            ->setRequired(true) 
            ->setLabel($this->l('Choisir entre   Professionnel OU  Particulier'));
            
            $format['company']=(new FormField())
            ->setName('company')
            ->setType('text')
            ->setRequired(false) 
            ->addAvailableValue('placeholder', 'Entrer  Raison  Sociale');
            
            
            
            $format['siret']= (new FormField())
            ->setName('siret')
            ->setType('text')
            ->addAvailableValue('placeholder', 'Entrer SIRET');
            
            

                if ($this->ask_for_partner_optin) {
                    $format['optin'] = (new FormField())
                        ->setName('optin')
                        ->setType('checkbox')
                        ->setLabel(
                            $this->trans(
                                'Receive offers from our partners',
                                [],
                                'Shop.Forms.Labels'
                            )
                            );
                }

                
  
    

    $params['fields'] = $this->array_insert_after($params['fields'], 'birthday', ['rpps_id'=>$format]);
    //////////////////////////
    $params['fields'] = $format;
   
		
        
        
        // return empty array because the new field already added with current fields
        
    }

    function array_insert_after( array $array, $key, array $new ) {
        $keys = array_keys( $array );
        $index = array_search( $key, $keys );
        $pos = false === $index ? count( $array ) : $index + 1;
    
        return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitUpdateCustomerGroup')) {
            $this->updateLine();
            
        }
    }
    
     function renderCustomerTable()
    {
        
        $sql = 'SELECT * FROM '._DB_PREFIX_.'customer';

        $customers = Db::getInstance()->executeS($sql);
        $groups = $this->getGroups();
        

        $html = '<table class="table">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>' . $this->l('ID') . '</th>';
        $html .= '<th>' . $this->l('First name') . '</th>';
        $html .= '<th>' . $this->l('Last name') . '</th>';
        $html .= '<th>' . $this->l('Email') . '</th>';
        $html .= '<th>' . $this->l('id_default_group') . '</th>';
        $html .= '<th>' . $this->l('Company') . '</th>';
        $html .= '<th>' . $this->l('Siret') . '</th>';
        $html .= '<th>' . $this->l('Action') . '</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($customers as $customer) {
            $html .= '<tr>';
            $html .= '<td>' . $customer['id_customer'] . '</td>';
            $html .= '<td>' . $customer['firstname'] . '</td>';
            $html .= '<td>' . $customer['lastname'] . '</td>';
            $html .= '<td>' . $customer['email'] . '</td>';
            $html .= '<td>' . $customer['id_default_group'] . '</td>';
            $html .= '<td>' . $customer['company'] . '</td>';
            $html .= '<td>' . $customer['siret'] . '</td>';
            
            
            
            $html .= '<td><form method="post" action="' .$this->updateLine(). '">';
            $html .= '<input type="hidden" name="id_customer" value="' . $customer['id_customer'] . '" />';
            $html .= '' . $this->printGroupDropdown($customer['id_default_group'], $customer, $groups) . '';
            $html .= '<button type="submit" name="submitUpdateCustomerGroup" class="btn btn-primary">' . $this->l('Update') . '</button>';
            $html .= '</form></td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    private function getGroups()
    {
        $groups = Group::getGroups($this->context->language->id);
        $groupArray = array();

        foreach ($groups as $group) {
            $groupArray[$group['id_group']] = $group['name'];
        }

        return $groupArray;
    }

    private function printGroupDropdown($id_group, $customer, $groups)
    {
        $html = '<select name="customer_group" class="form-control">';

        foreach ($groups as $groupId => $groupName) {
            $selected = ($groupId == $id_group) ? 'selected' : '';
            $html .= '<option   value="' . $groupId . '" ' . $selected . '>' . $groupName . '</option>';
        }

        $html .= '</select>';
        $html .= '<input type="hidden" name="id_customer" value="' . $customer['id_customer'] . '" />';

        return $html;
    }

    public function getContent()
    {
        return $this->renderCustomerTable(); 		
        
    }

   

    public function updateCustomer()
{
    $id_customer = (int)Tools::getValue('id_customer');
    $new_group_id = (int)Tools::getValue('customer_group'); // Supposons que vous vouliez mettre à jour le groupe de clients

    if ($id_customer && $new_group_id) {
        $customer = new Customer($id_customer);

        if (Validate::isLoadedObject($customer)) {
            $customer->id_default_group = $new_group_id;
            if ($customer->update()) {
                // Optionally, update customer groups association
                $customer->cleanGroups();
                $customer->addGroups(array($new_group_id));

                
            } else {
                $this->context->controller->errors[] = $this->l('Error: Customer ID = '.$id_customer.' not updated!');
            }
        } else {
            $this->context->controller->errors[] = $this->l('Error: Invalid customer ID!');
        }
    } else {
        $this->context->controller->errors[] = $this->l('Error: Missing customer ID or group ID!');
    }
}
public function updateLine(){
    $html="";
    
    
    $id_customer = (int)Tools::getValue('id_customer');
    $new_group_id = (int)Tools::getValue('customer_group');
    
    $query = "UPDATE ps_customer SET id_default_group= ".$new_group_id ." WHERE id_customer=".$id_customer;
    Db::getInstance()->Execute($query);
    return $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name.'&token='. Tools::getAdminTokenLite('AdminModules');
    
    
     	

}

public function hookactionObjectCustomerUpdateAfter()
{
    if (Tools::isSubmit('submitUpdateCustomerGroup')) {
        return $this->updateLine();
    }
	
}
  


}

