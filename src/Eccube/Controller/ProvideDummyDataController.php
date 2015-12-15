<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Controller;

use Eccube\Application;
use Eccube\Tests\ConcreateEccubeTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProvideDummyDataController extends AbstractController
{
    const MAKE_LIMIT = 100;
    const MAKE_DATA_CUSTOMER = 'createCustomer';
    const MAKE_DATA_PRODUCT = 'createProduct';
    const MAKE_DATA_ORDER = 'createOrder';

    private $DataFactory;
    private $MakeDataType;
    private $params;
    private $testRoot;
    private $app;
    private $total;
    private $typeFromQuery;

    public function __construct(){
        $this->testRoot = dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'Eccube'.DIRECTORY_SEPARATOR.'Tests'.DIRECTORY_SEPARATOR;
        include($this->testRoot.'ConcreateEccubeTestCase.php');
        $this->params = array();
        $this->DataFactory = new ConcreateEccubeTestCase();
        $this->typeFromQuery = array('c', 'p', 'o');
    }

    public function index(Application $app, Request $request)
    {
        if ($request->getMethod() !== 'GET') {
            throw new Exception('アクセスが不正です');
        }

        $type = $request->query->get('type');
        $unit = $request->query->get('unit');

        $this->app = $app;
        if (in_array($type, $this->typeFromQuery)) {
                switch($type){
                    case 'c' :
                        $this->setMakeDataType(self::MAKE_DATA_CUSTOMER);
                        break;
                    case 'p' :
                        $this->setMakeDataType(self::MAKE_DATA_PRODUCT);
                        break;
                    case 'o' :
                        $this->setMakeDataType(self::MAKE_DATA_ORDER);
                        break;
                }
        }

        if ($unit > self::MAKE_LIMIT ) {
            throw new Exception(self::MAKE_LIMIT.'以上は作成できません');
        }

        echo 'MakeStart!!</br>';
        echo 'Aleady made the '.$this->MakeDataType.'. </br>';
        $this->total = $unit;
        $this->DataFactory->setApplication($app);
        $this->make();
        exit();
    }

    private function setMakeDataType($type = null)
    {
        if(is_null($type)){
            throw new Exception('生成データのタイプを指定してください');
        }
        switch($type){
            case self::MAKE_DATA_CUSTOMER :
                $this->MakeDataType = self::MAKE_DATA_CUSTOMER;
                $this->params = array(null);
                break;
            case self::MAKE_DATA_PRODUCT :
                $this->MakeDataType = self::MAKE_DATA_PRODUCT;
                $this->params = array(null, 3);
                break;
            case self::MAKE_DATA_ORDER :
                $this->MakeDataType = self::MAKE_DATA_ORDER;
                $members = $this->app['eccube.repository.customer']->findAll();
                $this->params = $members;
                break;
        }
    }

    private function make()
    {
        set_time_limit(0);
        if($this->MakeDataType === self::MAKE_DATA_ORDER){
            echo 'Aleady made the '.$this->MakeDataType.'Object , '.count($this->params).' num. </br>';
            foreach($this->params as $Customer){
                call_user_func_array(array($this->DataFactory,$this->MakeDataType), array($Customer));
            }
            echo 'finished!!. </br>';
            exit();
        }
        for($i = 0; $i < $this->total; $i++){
            call_user_func_array(array($this->DataFactory,$this->MakeDataType), $this->params);
        }
        echo 'finished!!. </br>';
        exit();
    }
}
