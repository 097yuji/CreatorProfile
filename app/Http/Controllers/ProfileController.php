<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function profile()
    {
        // facebook ページアクセストークン
        $fb_token = 'EAAehU7Pb8ZAUBABCZAM1DBo3wrNswZBncFnKwACSSZBnXgbo6QDzhs5aZBI08pm7nPQAuNNN30JBwVr9mfLmNuyAt0Gz6iAW7Qziw7LJs0ItiraaVDF1YQEwzU50WnAdyX3RBCHIDPc64KoHwA3N826nVWJlh2W9Ds8Mtjx4YbNQdoYCMdduGlyhlwtlJnKcZD';

        // facebook ユーザーのページID
        $user_page_id = '112001893565201';

        // facebookのタイムラインから各投稿のIDとキャプションを取得
        $post_id = json_decode(@file_get_contents('https://graph.facebook.com/' . $user_page_id . '/feed?fields=message&access_token=' . $fb_token), true);

        // 特定のハッシュタグで絞った投稿の[投稿ID, キャプション]を 配列$hash_post[]に格納
        foreach ($post_id['data'] as $data) {
            if (strpos($data['message'], '#てすと') !== false) {
                $hash_post[] = [$data['id'], $data['message']];
            }
        }
        // 各投稿IDから投稿内容データを取得
        foreach ($hash_post as $post) {

            $post_data[] = [json_decode(@file_get_contents('https://graph.facebook.com/' . $post[0] . '/attachments?access_token=' . $fb_token), true), $post[1]];
        }


        // 投稿データから画像とキャプションを抜き出し、配列$fb_data[]に格納
        foreach ($post_data as $data) {
            // 投稿の添付画像が1枚の場合
            if ($data[0]['data'][0]['type'] == 'photo') {
                $fb_data[] = ['img' => $data[0]['data'][0]['media']['image']['src'], 'msg' => $data[1]];
            } else { // 投稿の添付画像が複数の場合
                foreach ($data[0]['data'][0]['subattachments']['data'] as $medias) {
                    if ($medias !== end($data[0]['data'][0]['subattachments']['data'])) { // 最終画像下以外キャプション表示しない
                        $fb_data[] = ['img' => $medias['media']['image']['src'], null];
                    } else {
                        $fb_data[] = ['img' => $medias['media']['image']['src'], 'msg' => $data[1]];
                    }
                }
            }
        }
        // dd($fb_data);

        return view('profile', compact('fb_data'));
    }
}
