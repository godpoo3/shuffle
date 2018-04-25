<?php

class ShuffleLunch
{
    private $filename = 'member_list.csv';
    private $path = '';
    private $member_number = 6;
    private $week_day = ['月', '水', '金'];
    private $plus_day = [0, 2, 4, 7, 9, 11];

    private $president = '武永修一';
    private $anti_first = ['牧瀬晃太', '眞鍋健二', '四谷豊', '近江大介','チョーピョーナイン','林瀚櫻'];
    private $anti_president = [];
    private $anti_shuffle = ['五十嵐重理'];

    private $charge_person = ' `ウー、ライ` ';
    private $header = '
    【%s月のシャッフルランチ】

お疲れさまです。
%s月のシャッフルランチのスケジュールと組み合わせをお送り致します。

####################################################################################
各日付の先頭の方が幹事を致しますようお願いします。
(支払いは幹事の方でなくて結構です。)
####################################################################################
';
    private $footer = '
    ----------------------------
グループ（順不同、敬称略）
----------------------------
■シャッフルランチとは？
1ヶ月に1回、定期的にランダムでメンバーを選び、
ランチタイムを一緒に過ごすことで社内のコミュニケーション促進させる制度です
■備考
・時間は12:00～13:00（あるいは12:30～13:30）の1時間でお願いいたします。
・グループ内で日程を調整し自由に変更して頂いて結構です。日程変更の連絡は不要です。
・領収書はオークファン宛で受け取り、オークファンの経費精算に提出いただくようお願いします。
・名簿の対象は当月1日までに入社した社員・契約社員・アルバイト・派遣社員としています。
・1日以降に入社される方は名簿に入っていない為、チームメンバーが声をかけて同じ会に連れて行ってあげてください。
・何かありましたら%sまで連絡をお願いします。
・1人当たりのランチの金額上限は1,200円です。
';

    public function __construct($path = '')
    {
        $this->path = $path;
        date_default_timezone_set('Asia/Tokyo');
        $month = date('n');
        $this->header = sprintf($this->header, $month, $month);
        $this->footer = sprintf($this->footer, $this->charge_person);
    }

    private function readFile()
    {
        $staff = [];
        try {
            $file = new SplFileObject($this->path . $this->filename);
            $file->setFlags(SplFileObject::READ_CSV);
            foreach ($file as $line) {
                $staff['name'][] = preg_replace('/(　|\s)/u', '', $line[2]);
            }
            return $staff;
        } catch (Exception $e) {
            echo "社員名簿を読み込めません。ファイル名を確認ください。";
            exit;
        }
    }

    private function setRandomMember($staffArray)
    {
        $shuffle = [];
        $num = 0;
        while (count($staffArray['name'])) {
            for ($i = 0; $i < $this->member_number; $i++) {
                $presidentFlag = false;
                while (count($staffArray['name'])) {
                    $rand = rand(0, count($staffArray['name']));
                    if (!empty($staffArray['name'][$rand])) {
                        // いろいろなリクエストを処理
                        if ($this->checkAntiShuffle($staffArray['name'][$rand])) {
                            array_splice($staffArray['name'], $rand, 1);
                            continue;
                        }
                        if ($i === 0 && $this->checkAntiFirst($staffArray['name'][$rand])) {
                            continue;
                        }
                        if ($this->checkPresident($staffArray['name'][$rand])) {
                            if (is_array($shuffle[$num]) && $this->checkAntiPresident($shuffle[$num])) {
                                continue;
                            }
                            $presidentFlag = true;
                        }
                        if ($presidentFlag && $this->checkAntiPresident(array($staffArray['name'][$rand]))){
                            continue;
                        }

                        $shuffle[$num][] = $staffArray['name'][$rand];
                        break;
                    }
                }
                //入れた人を削除
                array_splice($staffArray['name'], $rand, 1);
            }
            $num++;
        }
        return $shuffle;
    }

    private function checkPresident($name)
    {
        if ($name === $this->president) {
            return true;
        }
        return false;
    }

    private function checkAntiPresident($member)
    {
        $antiList = array_intersect($member, $this->anti_president);
        if (count($antiList)) {
            return true;
        }
        return false;
    }

    private function checkAntiFirst($name)
    {
        if (in_array($name, $this->anti_first)) {
            return true;
        }
        return false;
    }

    private function checkAntiShuffle($name)
    {
        if (in_array($name, $this->anti_shuffle)) {
            return true;
        }
        return false;
    }

    private function generateList($memberList)
    {
        $month = (string)date('n');
        $nextMonday = (int)date('j',strtotime('next Monday'));
        for ($i = 0; $i < count($memberList); $i++) {
            $day = (string)($nextMonday + $this->plus_day[$i % 6]);
            $key = $month . '/' . $day .'(' . $this->week_day[$i % 3] . ')';
            $finishList[$key][] = implode(', ', $memberList[$i]);
        }
        return $finishList;
    }

    public function getShuffleMember()
    {
        $staff = $this->readFile();
        $randomMember = $this->setRandomMember($staff);
        echo $this->header;
        $finishList = $this->generateList($randomMember);
        foreach ($finishList as $k => $v) {
            echo "\n" . $k;
            foreach ($v as $list) {
                echo "\n" . $list;
            }
            echo "\n\n";
        }
        echo $this->footer;
    }
}

$shuffle = new ShuffleLunch();
$shuffle->getShuffleMember();
