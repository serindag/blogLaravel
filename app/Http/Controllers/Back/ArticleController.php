<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $articles=Article::orderBy('created_at','ASC')->get();
        return view('back.articles.index',compact('articles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories=Category::all();
        return view('back.articles.create',compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'=>'min:3',
            'image'=>'required|image|mimes:png,jpg,jpeg|max:3000'
        ]);

        $isExist=Article::whereSlug(Str::slug($request->title,'-'))->first();
        if($isExist){
            toastr()->error($request->title.' adında bir makale zaten var');
            return redirect()->back();
        }

        $article=new Article;
        $article->title=$request->title;
        $article->category_id=$request->category;
        $article->content=$request->content;
        $article->slug=Str::slug($request->title,'-');

        if($request->hasFile('image')){
            $imageName=Str::slug($request->title,'-').'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('upload'),$imageName);
            $article->image='upload/'.$imageName;

        }
        $article->save();
        toastr()->success('Başarılı', 'Makale Başarı İle Oluşturuldu');
        return redirect()->route('admin.makaleler.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $article=Article::findOrFail($id);
        $categories=Category::all();
        return view('back.articles.update',compact('categories','article'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $isExist=Article::whereSlug(Str::slug($request->title,'-'))->whereNotIn('id',[$id])->first();
        if($isExist){
            toastr()->error($request->title.' adında bir makale zaten var');
            return redirect()->back();
        }

        $article=Article::findOrFail($id);
        $request->validate([
            'title'=>'min:3',
            'image'=>'image|mimes:png,jpg,jpeg|max:3000'
        ]);



        if($request->image)
        {
            if(File::exists($article->image))
         {
            File::delete(public_path($article->image));
         }

        }

        $article->title=$request->title;
        $article->category_id=$request->category;
        $article->content=$request->content;
        $article->slug=Str::slug($request->title,'-');



        if($request->hasFile('image')){
            $imageName=Str::slug($request->title,'-').'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('upload'),$imageName);
            $article->image='upload/'.$imageName;

        }
        $article->save();
        toastr()->success('Başarılı', 'Makale Başarı İle Güncellendi');
        return redirect()->route('admin.makaleler.index');
    }
    public function switch(Request $request)
    {
        $article=Article::findOrFail($request->id);
        $article->status=$request->statu=="true" ? 1 : 0 ;
        $article->save();

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function delete($id)
     {
         Article::find($id)->delete();
         toastr()->success('Makale silinen makalelere taşındı.');
         return redirect()->route('admin.makaleler.index');
     }

     public function trashed()
     {
         $articles=Article::onlyTrashed()->orderBy('deleted_at','desc')->get();
         return view('back.articles.trashed',compact('articles'));
     }

     public function recover($id)
     {
        $article=Article::onlyTrashed()->findOrFail($id)->restore();
        toastr()->success('Makale Başarı İle Yeniden Yüklendi.');
         return redirect()->route('admin.trashed.article');
     }
     public function harddelete($id)
     {
         $article=Article::onlyTrashed()->find($id);
         if(File::exists($article->image))
         {
            File::delete(public_path($article->image));
         }
         $article->forceDelete();
         toastr()->success('Makale Başarı İle Silindi.');
         return redirect()->route('admin.trashed.article');
     }
    public function destroy($id)
    {
        //
    }
}
