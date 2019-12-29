<?php
  class EntryFeeForm {
  
    public static function htmlFields() {
?>
          <div class="row-fluid">

            <div class="span6">
              <h4>SCCA Members, Competition Entry</h4>
 
              <div class="control-group" id="scca_comp_online-cg">
                <label class="control-label" for="scca_comp_online">Registering Online</label>
                <div class="controls">
                  <div class="input-prepend">
                    <span class="add-on">$</span>
                    <input class="input-mini" id="scca_comp_online" name="entry_fees[scca_comp_online]" type="text" />
                  </div>
                  <span class="label label-info">Required</span>
                </div>
              </div>

              <div class="control-group" id="scca_comp_onsite-cg">
                <label class="control-label" for="scca_comp_onsite">Registering On Site</label>
                <div class="controls">
                  <div class="input-prepend">
                    <span class="add-on">$</span>
                    <input class="input-mini" id="scca_comp_onsite" name="entry_fees[scca_comp_onsite]" type="text" />
                  </div>
                  <span class="label label-info">Required</span>
                </div>
              </div>

              <div data-reg="time_only_entry_fee">
                <h4>SCCA Members, Time Only Entry</h4>
 
                <div class="control-group" id="scca_to_online-cg">
                  <label class="control-label" for="scca_to_online">Registering Online</label>
                  <div class="controls">
                    <div class="input-prepend">
                      <span class="add-on">$</span>
                      <input class="input-mini" id="scca_to_online" name="entry_fees[scca_to_online]" type="text" />
                    </div>
                    <span class="label label-info">Required</span>
                  </div>
                </div>

                <div class="control-group" id="scca_to_onsite-cg">
                  <label class="control-label" for="scca_to_onsite">Registering On Site</label>
                  <div class="controls">
                    <div class="input-prepend">
                      <span class="add-on">$</span>
                      <input class="input-mini" id="scca_to_onsite" name="entry_fees[scca_to_onsite]" type="text" />
                    </div>
                    <span class="label label-info">Required</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="span6">

              <h4>Non SCCA Members, Competition Entry</h4>
 
              <div class="control-group" id="wknd_comp_online-cg">
                <label class="control-label" for="wknd_comp_online">Registering Online</label>
                <div class="controls">
                  <div class="input-prepend">
                    <span class="add-on">$</span>
                    <input class="input-mini" id="wknd_comp_online" name="entry_fees[wknd_comp_online]" type="text" />
                  </div>
                  <span class="label label-info">Required</span>
                </div>
              </div>

              <div class="control-group" id="wknd_comp_onsite-cg">
                <label class="control-label" for="wknd_comp_onsite">Registering On Site</label>
                <div class="controls">
                  <div class="input-prepend">
                    <span class="add-on">$</span>
                    <input class="input-mini" id="wknd_comp_onsite" name="entry_fees[wknd_comp_onsite]" type="text" />
                  </div>
                  <span class="label label-info">Required</span>
                </div>
              </div>

              <div data-reg="time_only_entry_fee">
                <h4>Non SCCA Members, Time Only Entry</h4>
 
                <div class="control-group" id="wknd_to_online-cg">
                  <label class="control-label" for="wknd_to_online">Registering Online</label>
                  <div class="controls">
                    <div class="input-prepend">
                      <span class="add-on">$</span>
                      <input class="input-mini" id="wknd_to_online" name="entry_fees[wknd_to_online]" type="text" />
                    </div>
                    <span class="label label-info">Required</span>
                  </div>
                </div>

                <div class="control-group" id="wknd_to_onsite-cg">
                  <label class="control-label" for="wknd_to_onsite">Registering On Site</label>
                  <div class="controls">
                    <div class="input-prepend">
                      <span class="add-on">$</span>
                      <input class="input-mini" id="wknd_to_onsite" name="entry_fees[wknd_to_onsite]" type="text" />
                    </div>
                    <span class="label label-info">Required</span>
                  </div>
                </div>

               </div>

            </div>

          </div>

          <hr/>

          <div class="row-fluid">
            <div class="span6">
              <h4>Payment Options</h4>

              <div class="control-group" id="online_payment-cg">
                <label class="control-label" for="online_payment">Online Payment Fee</label>
                <div class="controls">
                  <div class="input-prepend">
                    <span class="add-on">$</span>
                    <input class="input-mini" id="online_payment" name="entry_fees[online_payment]" type="text" />
                  </div>
                  <span class="label label-info">Required</span>
                  <div class="help-block">
                    <i class="icon-info-sign"></i>
                    Fee the organization charges for accepting an online payment; $0.00 is allowed
                  </div>
                </div>
              </div>

              <div class="control-group" id="online_conditional-cg">
                <label class="control-label" for="online_conditional">Conditional Online Discount</label>
                <div class="controls">
                  <select class="input-small" id="online_conditional" name="entry_fees[online_conditional]">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                  </select>
                  <span class="label label-info">Required</span>
                  <div class="help-block">
                    <i class="icon-info-sign"></i>
                    If 'Yes', online entry fee rate will only apply if entry is paid for online
                    before registration closes. If 'No' online entry fee will apply to any
                    entrant registering before closing time.
                  </div>
                </div>
              </div>






            </div>
          </div>
<?php
    }
  }
?>
