<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function profile()
    {
        // facebook ページアクセストークン (API申請用テストアカウントのページ用)
        $fb_token = 'EAAehU7Pb8ZAUBAPqMz63JRsRxIoh1NosnZA665AOZA8BademKwM4fA0GSWUfXj6xzR44cazQr1MjWYAoCalBJ8AZB2gbv7Rstc7EaOAdiYQlNS3PeoBoxwt9k7beQSfjNgWZBoELQEHslcBO3tgt2lAlcVkHSMgs8MQXmFJ6O4O9dso3XvUjSC70zaJYBZAq4ZD';

        // facebook ユーザーのページID (API申請用テストアカウントのページ用)
        $user_page_id = '108554017352373';

        // facebookのタイムラインから各投稿のIDとキャプションを取得
        $post_id = json_decode(@file_get_contents('https://graph.facebook.com/' . $user_page_id . '/feed?fields=message&access_token=' . $fb_token), true);

        // 特定のハッシュタグで絞った投稿の[投稿ID, キャプション]を 配列$hash_post[]に格納
        foreach ($post_id['data'] as $data) {
            if (isset($data['message']) && strpos($data['message'], '#てすと') !== false) {
                $hash_post[] = [$data['id'], $data['message']];
            }
        }

        // 各投稿IDから投稿内容データを取得
        foreach ($hash_post as $post) {

            $post_data[] = [json_decode(@file_get_contents('https://graph.facebook.com/' . $post[0] . '/attachments?access_token=' . $fb_token), true), $post[1]];
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
        // dd($fb_data);

        return view('profile', compact('fb_data'));
    }
}
