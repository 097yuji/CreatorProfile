<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function profile()
    {
        // アプリ管理者<Dev Snapshot>の無期限ユーザーアクセストークン
        $user_token = 'EAAehU7Pb8ZAUBAJS3fY9aevMqZC6Mo7XqHuDLmTGQx6AHRqSkPFvkQOb947mER0sKwHUpJw1sAQeU8kFgVUGdhPAeoc12aJeJl4xwkImyLhg2bny6UZB8Oliljqa8wUmpZAKjDg7ZB2h012aMxWNMBN3ZCO3j3cw2BAkkkWjewnXqLSwezCpyi';

        // クリエーターのfacebookページID
        $user_page_id = '109639097248642'; // たぶんここはDBのクリエータ情報からページidを取得してくるのかな

        // ページIDから無期限ページアクセストークンを取得    ◆◆たぶんクリエータ情報のDB登録時に保存される無期限トークンを参照する形かな
        $get_token = json_decode(@file_get_contents('https://graph.facebook.com/' . $user_page_id . '?fields=access_token&access_token=' . $user_token), true);
        $page_token = $get_token['access_token'];


        // facebookのタイムラインから各投稿のIDとキャプションを取得
        $post_id = json_decode(@file_get_contents('https://graph.facebook.com/' . $user_page_id . '/feed?fields=message&access_token=' . $page_token), true);

        // 特定のハッシュタグで絞った投稿の[投稿ID, キャプション]を 配列$hash_post[]に格納
        foreach ($post_id['data'] as $data) {
            if (isset($data['message']) && strpos($data['message'], '#てすと') !== false) {
                $hash_post[] = [$data['id'], $data['message']];
            }
        }

        // 各投稿IDから投稿内容データを取得
        foreach ($hash_post as $post) {

            $post_data[] = [json_decode(@file_get_contents('https://graph.facebook.com/' . $post[0] . '/attachments?access_token=' . $page_token), true), $post[1]];
        }
        // dd($post_data);


        // 投稿データから画像とキャプションを抜き出し、配列$fb_data[]に格納
        foreach ($post_data as $data) {
            if (isset($data[0]['data'][0])) { // 添付データがある場合

                if ($data[0]['data'][0]['type'] == 'photo') { // 投稿の添付画像が1枚の場合
                    $fb_data[] = ['img' => $data[0]['data'][0]['media']['image']['src'], 'msg' => $data[1]];
                } elseif ($data[0]['data'][0]['type'] == 'album') { // 投稿の添付画像が複数の場合
                    foreach ($data[0]['data'][0]['subattachments']['data'] as $medias) {
                        if ($medias !== end($data[0]['data'][0]['subattachments']['data'])) { // 最終画像下以外キャプション表示しない
                            $fb_data[] = ['img' => $medias['media']['image']['src'], null];
                        } else {
                            $fb_data[] = ['img' => $medias['media']['image']['src'], 'msg' => $data[1]];
                        }
                    }
                }
            }
        }

        return view('profile', compact('fb_data'));
    }

    public function profile_register()
    {
    }
}
