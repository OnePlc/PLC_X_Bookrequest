<?php
/**
 * BookrequestController.php - Main Controller
 *
 * Main Controller Bookrequest Module
 *
 * @category Controller
 * @package Bookrequest
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

namespace OnePlace\Bookrequest\Controller;

use Application\Controller\CoreController;
use Application\Model\CoreEntityModel;
use OnePlace\Bookrequest\Model\Bookrequest;
use OnePlace\Bookrequest\Model\BookrequestTable;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;

class BookrequestController extends CoreController {
    /**
     * Bookrequest Table Object
     *
     * @since 1.0.0
     */
    private $oTableGateway;

    /**
     * BookrequestController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param BookrequestTable $oTableGateway
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter,BookrequestTable $oTableGateway,$oServiceManager) {
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'bookrequest-single';
        parent::__construct($oDbAdapter,$oTableGateway,$oServiceManager);

        if($oTableGateway) {
            # Attach TableGateway to Entity Models
            if(!isset(CoreEntityModel::$aEntityTables[$this->sSingleForm])) {
                CoreEntityModel::$aEntityTables[$this->sSingleForm] = $oTableGateway;
            }
        }
    }

    /**
     * Bookrequest Index
     *
     * @since 1.0.0
     * @return ViewModel - View Object with Data from Controller
     */
    public function indexAction() {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('bookrequest');

        # Add Buttons for breadcrumb
        $this->setViewButtons('bookrequest-index');

        # Set Table Rows for Index
        $this->setIndexColumns('bookrequest-index');

        # Get Paginator
        $oPaginator = $this->oTableGateway->fetchAll(true);
        $iPage = (int) $this->params()->fromQuery('page', 1);
        $iPage = ($iPage < 1) ? 1 : $iPage;
        $oPaginator->setCurrentPageNumber($iPage);
        $oPaginator->setItemCountPerPage(3);

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('bookrequest-index',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        return new ViewModel([
            'sTableName'=>'bookrequest-index',
            'aItems'=>$oPaginator,
        ]);
    }

    /**
     * Bookrequest Add Form
     *
     * @since 1.0.0
     * @return ViewModel - View Object with Data from Controller
     */
    public function addAction() {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('bookrequest');

        # Get Request to decide wether to save or display form
        $oRequest = $this->getRequest();

        # Display Add Form
        if(!$oRequest->isPost()) {
            # Add Buttons for breadcrumb
            $this->setViewButtons('bookrequest-single');

            # Load Tabs for View Form
            $this->setViewTabs($this->sSingleForm);

            # Load Fields for View Form
            $this->setFormFields($this->sSingleForm);

            # Log Performance in DB
            $aMeasureEnd = getrusage();
            $this->logPerfomance('bookrequest-add',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

            return new ViewModel([
                'sFormName' => $this->sSingleForm,
            ]);
        }

        # Get and validate Form Data
        $aFormData = $this->parseFormData($_REQUEST);

        # Save Add Form
        $oBookrequest = new Bookrequest($this->oDbAdapter);
        $oBookrequest->exchangeArray($aFormData);
        $iBookrequestID = $this->oTableGateway->saveSingle($oBookrequest);
        $oBookrequest = $this->oTableGateway->getSingle($iBookrequestID);

        # Save Multiselect
        $this->updateMultiSelectFields($_REQUEST,$oBookrequest,'bookrequest-single');

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('bookrequest-save',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        # Display Success Message and View New Bookrequest
        $this->flashMessenger()->addSuccessMessage('Bookrequest successfully created');
        return $this->redirect()->toRoute('bookrequest',['action'=>'view','id'=>$iBookrequestID]);
    }

    /**
     * Bookrequest Edit Form
     *
     * @since 1.0.0
     * @return ViewModel - View Object with Data from Controller
     */
    public function editAction() {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('bookrequest');

        # Get Request to decide wether to save or display form
        $oRequest = $this->getRequest();

        # Display Edit Form
        if(!$oRequest->isPost()) {

            # Get Bookrequest ID from URL
            $iBookrequestID = $this->params()->fromRoute('id', 0);

            # Try to get Bookrequest
            try {
                $oBookrequest = $this->oTableGateway->getSingle($iBookrequestID);
            } catch (\RuntimeException $e) {
                echo 'Bookrequest Not found';
                return false;
            }

            # Attach Bookrequest Entity to Layout
            $this->setViewEntity($oBookrequest);

            # Add Buttons for breadcrumb
            $this->setViewButtons('bookrequest-single');

            # Load Tabs for View Form
            $this->setViewTabs($this->sSingleForm);

            # Load Fields for View Form
            $this->setFormFields($this->sSingleForm);

            # Log Performance in DB
            $aMeasureEnd = getrusage();
            $this->logPerfomance('bookrequest-edit',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

            return new ViewModel([
                'sFormName' => $this->sSingleForm,
                'oBookrequest' => $oBookrequest,
            ]);
        }

        $iBookrequestID = $oRequest->getPost('Item_ID');
        $oBookrequest = $this->oTableGateway->getSingle($iBookrequestID);

        # Update Bookrequest with Form Data
        $oBookrequest = $this->attachFormData($_REQUEST,$oBookrequest);

        # Save Bookrequest
        $iBookrequestID = $this->oTableGateway->saveSingle($oBookrequest);

        $this->layout('layout/json');

        $aFormData = $this->parseFormData($_REQUEST);

        # Save Multiselect
        $this->updateMultiSelectFields($aFormData,$oBookrequest,'bookrequest-single');

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('bookrequest-save',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        # Display Success Message and View New User
        $this->flashMessenger()->addSuccessMessage('Bookrequest successfully saved');
        return $this->redirect()->toRoute('bookrequest',['action'=>'view','id'=>$iBookrequestID]);
    }

    /**
     * Bookrequest View Form
     *
     * @since 1.0.0
     * @return ViewModel - View Object with Data from Controller
     */
    public function viewAction() {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('bookrequest');

        # Get Bookrequest ID from URL
        $iBookrequestID = $this->params()->fromRoute('id', 0);

        # Try to get Bookrequest
        try {
            $oBookrequest = $this->oTableGateway->getSingle($iBookrequestID);
        } catch (\RuntimeException $e) {
            echo 'Bookrequest Not found';
            return false;
        }

        # Attach Bookrequest Entity to Layout
        $this->setViewEntity($oBookrequest);

        # Add Buttons for breadcrumb
        $this->setViewButtons('bookrequest-view');

        # Load Tabs for View Form
        $this->setViewTabs($this->sSingleForm);

        # Load Fields for View Form
        $this->setFormFields($this->sSingleForm);

        /**
         * @addedtobook
         * @requires 1.0.5
         * @campatibleto master-dev
         */
        $aPartialData = [
            'aMatchingResults'=>$oBookrequest->getMatchingResults(),
            'aViewCriterias' =>$oBookrequest->getMatchingCriterias(),
        ];
        $this->setPartialData('matching',$aPartialData);
        /**
         * @addedtobookend
         */

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('bookrequest-view',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        return new ViewModel([
            'sFormName'=>$this->sSingleForm,
            'oBookrequest'=>$oBookrequest,
        ]);
    }

    /**
     * @addedtobook
     * @requires 1.0.5
     * @campatibleto master-dev
     */
    /**
     * Close Request as successful
     *
     * @since 1.0.0
     */
    public function successAction() {
        $aInfo = explode('-',$this->params()->fromRoute('id','0-0'));
        $iRequestID = $aInfo[0];
        $iBookID = $aInfo[1];

        try {
            $oBookTable = CoreController::$oServiceManager->get(\OnePlace\Book\Model\BookTable::class);
        } catch(\RuntimeException $e) {
            echo 'could not load book table';
            return false;
        }

        # check if state tag is active
        $oTag = CoreController::$aCoreTables['core-tag']->select(['tag_key'=>'state']);
        if(count($oTag) > 0) {
            $oTagState = $oTag->current();
            # check if we find success state tag for book request
            $oEntityTagRequest = CoreController::$aCoreTables['core-entity-tag']->select(['tag_value'=>'success','tag_idfs'=>$oTagState->Tag_ID,'entity_form_idfs'=>'bookrequest-single']);
            if(count($oEntityTagRequest) > 0) {
                $oEntityTagSuccess = $oEntityTagRequest->current();
                $this->oTableGateway->updateAttribute('state_idfs',$oEntityTagSuccess->Entitytag_ID,'Bookrequest_ID',$iRequestID);
                $this->oTableGateway->updateAttribute('book_idfs',$iBookID,'Bookrequest_ID',$iRequestID);
            }
            # check if we find sold state tag for book
            $oEntityTagBook = CoreController::$aCoreTables['core-entity-tag']->select(['tag_value'=>'sold','tag_idfs'=>$oTagState->Tag_ID,'entity_form_idfs'=>'book-single']);
            if(count($oEntityTagBook) > 0) {
                $oEntityTagSold = $oEntityTagBook->current();
                $oBookTable->updateAttribute('state_idfs',$oEntityTagSold->Entitytag_ID,'Book_ID',$iRequestID);
            }
        }

        # Display Success Message and View New Bookrequest
        $this->flashMessenger()->addSuccessMessage('Bookrequest successfully closed');
        return $this->redirect()->toRoute('bookrequest',['action'=>'view','id'=>$iRequestID]);
    }
    /**
     * @addedtobookend
     */
}
