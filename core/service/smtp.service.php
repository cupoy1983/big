<?php
/**
 * 邮件服务类
 * @author jobin
 *
 */
class SmtpService{
    public function loopSendMail($mail){
        FDB::query('UPDATE '.FDB::table('smtp').' SET batch_count=0 WHERE auto_reset = 1 AND status=1 AND batch_count >= batch_limit');
        $stmp_arr = FDB::fetchFirst('select * from '.FDB::table('smtp').' where  (batch_count < batch_limit || batch_limit = 0) and status=1 order by batch_count asc');
		$mail->setStmp($stmp_arr);
        return $stmp_arr;
    }
}
?>
