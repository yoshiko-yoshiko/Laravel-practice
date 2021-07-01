<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Memo;
use App\Tag;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('create');
    }

    public function create()
    {
        return view('create');
    }

    public function store(Request $request)
    {
        $data = $request->all();
        //dd($data);
        // POSTされたデータをDB（memosテーブル）に挿入
        // MEMOモデルにDBへ保存する命令を出す

        //同じタグがあることを判定
        //SQL文ではcompactは使えない
        $exist_tag = Tag::where('name',$data['tag'])->where('user_id',$data['user_id'])->first();
        if ( empty($exist_tag['id']) ) {
            $tag_id = Tag::insertGetId(['name' => $data['tag'],'user_id' => $data['user_id']]);
        }
        else{
            $tag_id = $exist_tag['id'];
        }
        //タグを先にインサート
        $tag_id = Tag::insertGetId(['name' => $data['tag'],'user_id' => $data['user_id']]);
        //dd('$tag_id');

        // 三つの挿入
        $memo_id = Memo::insertGetId([
            'content' => $data['content'],
            'user_id' => $data['user_id'],
            'tag_id' => $tag_id,
            'status' => 1
        ]);
        // リダイレクト処理→別のページに飛ばす
        return redirect()->route('home');
    }

    public function edit($id){
        // 該当するIDのメモをデータベースから取得
        $user = Auth::user();
        $memo = Memo::where('status', 1)->where('id', $id)->where('user_id', $user['id'])
        ->first();
        //   dd($memo);
        return view('edit',compact('memo'));
    }

    public function update(Request $request, $id)
    {
        $inputs = $request->all();
        // dd($inputs);
        Memo::where('id', $id)->update(['content' => $inputs['content'],'tag_id' => $inputs['tag_id']]);
        return redirect()->route('home');
    }

    public function delete(Request $request, $id)
    {
        $inputs = $request->all();
        // dd($inputs);
        //論理削除なので、2
        Memo::where('id', $id)->update(['status' => 2]);
        // Memo::where('id', $id)->delete(); こいつだと全部消える
        return redirect()->route('home')->with('success','メモの削除が完了しました。');
    }
}

