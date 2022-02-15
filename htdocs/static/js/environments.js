
var Environments = {
    bpmVersion: null,
    mode: null,
    draftInterval: null,
    sendDraftJsonToLocalStorage: null,

    set: function (data) {
        this.bpmVersion = data['bpm-version'];
        this.mode = data['mode'];
        this.draftInterval = data['draft_interval'];
        this.sendDraftJsonToLocalStorage = data['send_draft_json_to_local_storage'];
    },
    isProductionMode: function() {
        return this.mode == "production" ? true : false;
    },
}
