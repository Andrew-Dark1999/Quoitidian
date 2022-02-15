<?php
/**
 * EntityEventsModel
 */

class EntityEventsModel{

    //***Event names
    const EVENT_CLICK      = 'click';
    const EVENT_READY      = 'ready';
    const EVENT_DESTROY    = 'destroy';



    //***Element id for events

    //EditView
    const EID_EDIT_VIEW_SAVE              = 'ev_save';
    const EID_LIST_VIEW_SAVE              = 'lv_save';


    //EditView SDM
    const EID_EDIT_VIEW_SDM_ADD           = 'ev_sdm_add';
    const EID_EDIT_VIEW_SDM_REMOVE        = 'ev_sdm_remove';
    const EID_EDIT_VIEW_SDM_EDIT          = 'ev_sdm_edit';
    const EID_EDIT_VIEW_SDM_SELECT_ITEM   = 'ev_sdm_select_item';

    //ListView SDM
    const EID_LIST_VIEW_SDM_ADD           = 'lv_sdm_add';
    const EID_LIST_VIEW_SDM_REMOVE        = 'lv_sdm_remove';
    const EID_LIST_VIEW_SDM_EDIT          = 'lv_sdm_edit';
    const EID_LIST_VIEW_SDM_SELECT_ITEM   = 'lv_sdm_select_item';

    //InlineSDM
    const ELEMENT_INLINE_SDM_ADD              = 'inline_sdm_add';
    const ELEMENT_INLINE_SDM_REMOVE           = 'inline_sdm_remove';
    const ELEMENT_INLINE_SAVE                 = 'save_sdm_remove';







}
