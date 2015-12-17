<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define('UB_USER', '5e319884847c030a1e83707ba7af5126');
define('UB_PASS', "");
define('UB_TEST_CLIENT_ID', 'e85e7926-9642-11e4-b1fb-22000b252516');


$url = "https://api.unbounce.com/pages/".UB_TEST_CLIENT_ID."/leads";
?>



<form method="POST" action="<?php echo $url; ?>">
    
    <label>Unbounce User : <?php echo UB_USER ?></label> <br>
    <label>Client : Beverly Hills</label> <br>
    <label>ID : <?php echo UB_TEST_CLIENT_ID ?></label> <br>
    
    <br><br>
        
    
    <label>Caller ID :</label><input type="text" name="form_submission[form_data][callerid]" value="11111111"> <br>
    <label>GSM Number:</label><input type="text" name="form_submission[form_data][gsm_number]" value="0503581396"> <br>
    <label>Forward number:</label><input type="text" name="form_submission[form_data][forward_number]" value="22222222"> <br>
    <label>Call Start:</label><input type="text" name="form_submission[form_data][call_start]" value="2015-01-12 10:53:47"> <br>
    <label>Call End:</label><input type="text" name="form_submission[form_data][call_end]" value="2015-01-12 10:53:57"> <br>
    <label>IP :</label><input type="text" name="form_submission[submitter_ip]" value="127.0.0.1"> <br>
    <label>Variant :</label><input type="text" name="form_submission[variant_id]" value="a" readonly="readonly"> <br>
    <input type="submit">
    
</form>