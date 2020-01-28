<?php
/**
 * ApiController.php - Bookrequest Api Controller
 *
 * Main Controller for Bookrequest Api
 *
 * @category Controller
 * @package Application
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

namespace OnePlace\Bookrequest\Controller;

use Application\Controller\CoreController;
use OnePlace\Bookrequest\Model\BookrequestTable;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;

class ApiController extends CoreController {
    /**
     * Bookrequest Table Object
     *
     * @since 1.0.0
     */
    private $oTableGateway;

    /**
     * ApiController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param BookrequestTable $oTableGateway
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter,BookrequestTable $oTableGateway,$oServiceManager) {
        parent::__construct($oDbAdapter,$oTableGateway,$oServiceManager);
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'bookrequest-single';
    }

    /**
     * API Home - Main Index
     *
     * @return bool - no View File
     * @since 1.0.0
     */
    public function indexAction() {
        $this->layout('layout/json');

        $aReturn = ['state'=>'success','message'=>'Welcome to onePlace Bookrequest API'];
        echo json_encode($aReturn);

        return false;
    }

    /**
     * List all Entities of Bookrequests
     *
     * @return bool - no View File
     * @since 1.0.0
     */
    public function listAction() {
        $this->layout('layout/json');

        $bSelect2 = true;

        /**
         * todo: enforce to use /api/contact instead of /contact/api so we can do security checks in main api controller
        if(!\Application\Controller\ApiController::$bSecurityCheckPassed) {
        # Print List with all Entities
        $aReturn = ['state'=>'error','message'=>'no direct access allowed','aItems'=>[]];
        echo json_encode($aReturn);
        return false;
        }
         **/

        $aItems = [];

        # Get All Bookrequest Entities from Database
        $oItemsDB = $this->oTableGateway->fetchAll(false);
        if(count($oItemsDB) > 0) {
            foreach($oItemsDB as $oItem) {
                if($bSelect2) {
                    $aItems[] = ['id'=>$oItem->getID(),'text'=>$oItem->getLabel()];
                } else {
                    $aItems[] = $oItem;
                }

            }
        }

        /**
         * Build Select2 JSON Response
         */
        $aReturn = [
            'state'=>'success',
            'results' => $aItems,
            'pagination' => (object)['more'=>false],
        ];

        # Print List with all Entities
        echo json_encode($aReturn);

        return false;
    }

    /**
     * Get a single Entity of Bookrequest
     *
     * @return bool - no View File
     * @since 1.0.0
     */
    public function getAction() {
        $this->layout('layout/json');

        # Get Bookrequest ID from route
        $iItemID = $this->params()->fromRoute('id', 0);

        # Try to get Bookrequest
        try {
            $oItem = $this->oTableGateway->getSingle($iItemID);
        } catch (\RuntimeException $e) {
            # Display error message
            $aReturn = ['state'=>'error','message'=>'Bookrequest not found','oItem'=>[]];
            echo json_encode($aReturn);
            return false;
        }

        # Print Entity
        $aReturn = ['state'=>'success','message'=>'Bookrequest found','oItem'=>$oItem];
        echo json_encode($aReturn);

        return false;
    }
}
