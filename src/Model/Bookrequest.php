<?php
/**
 * Bookrequest.php - Bookrequest Entity
 *
 * Entity Model for Bookrequest
 *
 * @category Model
 * @package Bookrequest
 * @author Verein onePlace
 * @copyright (C) 2020 Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

namespace OnePlace\Bookrequest\Model;

use Application\Controller\CoreController;
use Application\Model\CoreEntityModel;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Sql\Select;

class Bookrequest extends CoreEntityModel {
    /**
     * Request Title
     *
     * @var string $label
     * @since 1.0.0
     */
    public $label;
    /**
     * @addedtobook
     * @requires 1.0.5
     * @campatibleto master-dev
     */
    /**
     * Request linked Book
     *
     * @var int $book_idfs linked book (after successful match)
     * @since 1.0.0
     */
    public $book_idfs;
    /**
     * @addedtobookend
     */

    /**
     * Bookrequest constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @since 1.0.0
     */
    public function __construct($oDbAdapter) {
        parent::__construct($oDbAdapter);

        # Set Single Form Name
        $this->sSingleForm = 'bookrequest-single';

        # Attach Dynamic Fields to Entity Model
        $this->attachDynamicFields();
    }

    /**
     * Set Entity Data based on Data given
     *
     * @param array $aData
     * @since 1.0.0
     */
    public function exchangeArray(array $aData) {
        $this->id = !empty($aData['Bookrequest_ID']) ? $aData['Bookrequest_ID'] : 0;
        $this->label = !empty($aData['label']) ? $aData['label'] : '';

        /**
         * @addedtobook
         * @requires 1.0.5
         * @campatibleto master-dev
         */
        $this->book_idfs = !empty($aData['book_idfs']) ? $aData['book_idfs'] : '';
        /**
         * @addedtobookend
         */

        $this->updateDynamicFields($aData);
    }

    /**
     * @addedtobook
     * @requires 1.0.5
     * @campatibleto master-dev
     */
    /**
     * Get Matching Articles to Request
     *
     * @return array Matchings Results as Book Entities
     * @since 1.0.0
     */
    public function getMatchingResults() {
        # Init Book Table
        if(!array_key_exists('book',CoreController::$aCoreTables)) {
            CoreController::$aCoreTables['book'] = new TableGateway('book',CoreController::$oDbAdapter);
        }
        # Init Tags Table
        if(!array_key_exists('core-tag',CoreController::$aCoreTables)) {
            CoreController::$aCoreTables['core-tag'] = new TableGateway('core_tag',CoreController::$oDbAdapter);
        }
        # Init Entity Tags Table
        if(!array_key_exists('core-entity-tag',CoreController::$aCoreTables)) {
            CoreController::$aCoreTables['core-entity-tag'] = new TableGateway('core_entity_tag',CoreController::$oDbAdapter);
        }
        # Init Entity Tags Table
        if(!array_key_exists('core-entity-tag-entity',CoreController::$aCoreTables)) {
            CoreController::$aCoreTables['core-entity-tag-entity'] = new TableGateway('core_entity_tag_entity',CoreController::$oDbAdapter);
        }

        try {
            $oBookResultTbl = CoreController::$oServiceManager->get(\OnePlace\Book\Model\BookTable::class);
        } catch(\RuntimeException $e) {
            throw new \RuntimeException(sprintf(
                'Could not load entity table needed for matching'
            ));
        }

        # Init Empty List
        $aMatchedArticles = [];

        # Get Matches Book by Category
        $aMatchedArticles = $this->matchByAttribute('category');
        $bCategoryFilterActive = (count($aMatchedArticles) > 0) ? true : false;
        $bModelFilterActive = false;
        if(!$bCategoryFilterActive) {
            $aMatchedArticles = $this->matchByAttribute('model');
            $bModelFilterActive = (count($aMatchedArticles) > 0) ? true : false;
        } else {
            # todo: Make AND filter
        }

        if(!$bCategoryFilterActive && !$bModelFilterActive) {
            $aMatchedArticles = $this->matchByAttribute('manufacturer');
        } else {
           # todo: Make AND filter
        }

        /**
         * Enforce State for Matching Results
         */
        if(count($aMatchedArticles) > 0) {
            # Check if state tag is present
            $sTagKey = 'state';
            $oTag = CoreController::$aCoreTables['core-tag']->select(['tag_key'=>$sTagKey]);
            if(count($oTag) > 0) {
                # check if enforce state option for request is active
                $sState = CoreController::$aGlobalSettings['bookrequest-enforce-state'];
                if($sState != '') {
                    # enforce state for results
                    $aEnforcedMatches = [];
                    $oTag = $oTag->current();
                    $oEntityTag = CoreController::$aCoreTables['core-entity-tag']->select(['tag_value' => $sState, 'tag_idfs' => $oTag->Tag_ID]);

                    # check if state exists for entity
                    if (count($oEntityTag) > 0) {
                        $oEntityTag = $oEntityTag->current();
                        # compare state for all matches, only add matching
                        foreach (array_keys($aMatchedArticles) as $sMatchKey) {
                            $oMatch = $aMatchedArticles[$sMatchKey];
                            if ($oMatch->getSelectFieldID('state_idfs') == $oEntityTag->Entitytag_ID) {
                                $aEnforcedMatches[] = $oMatch;
                            }
                        }
                    }
                    # return curated results
                    $aMatchedArticles = $aEnforcedMatches;
                }
            }
        }

        return $aMatchedArticles;
    }

    private function matchByAttribute($sTagKey) {
        try {
            $oBookResultTbl = CoreController::$oServiceManager->get(\OnePlace\Book\Model\BookTable::class);
        } catch(\RuntimeException $e) {
            throw new \RuntimeException(sprintf(
                'Could not load entity table needed for matching'
            ));
        }
        $aMatchedArticles = [];
        # Match Book by Category - only if category tag is found
        $oTag = CoreController::$aCoreTables['core-tag']->select(['tag_key'=>$sTagKey]);
        if(count($oTag)) {
            $oTag = $oTag->current();
            # 1. Get all Categories linked to this request
            $oCategorySel = new Select(CoreController::$aCoreTables['core-entity-tag-entity']->getTable());
            $oCategorySel->join(['cet'=>'core_entity_tag'],'cet.Entitytag_ID = core_entity_tag_entity.entity_tag_idfs');
            $oCategorySel->where(['entity_idfs'=>$this->getID(),'cet.tag_idfs = '.$oTag->Tag_ID,'entity_type'=>'bookrequest']);
            $oMyCats = CoreController::$aCoreTables['core-entity-tag']->selectWith($oCategorySel);
            if(count($oMyCats) > 0) {
                # Loop over all matched categories
                foreach($oMyCats as $oMyCat) {
                    # Find book with the same category
                    $oMatchedArtsByCat = CoreController::$aCoreTables['core-entity-tag-entity']->select(['entity_tag_idfs'=>$oMyCat->Entitytag_ID,'entity_type'=>'book']);
                    if(count($oMatchedArtsByCat) > 0) {
                        foreach($oMatchedArtsByCat as $oMatchRow) {
                            $aMatchedArticles[] = $oBookResultTbl->getSingle($oMatchRow->entity_idfs);
                        }
                    }
                }
            }
        }

        return $aMatchedArticles;
    }

    /**
     * Get Matching Criterias for Results
     *
     * @return array list with criterias
     * @since 1.0.0
     */
    public function getMatchingCriterias() {
        $aMatchingCriterias = [];

        # Init Criterias Table
        if(!array_key_exists('book-request-criteria',CoreController::$aCoreTables)) {
            CoreController::$aCoreTables['book-request-criteria'] = new TableGateway('bookrequest_criteria',CoreController::$oDbAdapter);
        }

        $oCriteriasFromDB = CoreController::$aCoreTables['book-request-criteria']->select();
        foreach($oCriteriasFromDB as $oCrit) {
            $aMatchingCriterias[$oCrit->criteria_entity_key] = (array)$oCrit;
        }

        return $aMatchingCriterias;
    }
    /**
     * @addedtobookend
     */
}