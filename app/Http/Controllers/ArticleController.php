<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\ArticlesDeveloper;
use App\Models\BrandGarment;
use App\Models\ModelGarment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('article.browse');
    }

    public function list($search = null){
        $user = Auth::user();
        $paginate = request('paginate') ?? 10;
        $data = Article::with('category')->where(function($query) use ($search){
                    $query->OrWhereRaw($search ? "id = '$search'" : 1)
                    ->OrWhereRaw($search ? "name like '%$search%'" : 1);
                    // ->OrWhereRaw($search ? "phone like '%$search%'" : 1);
                    })
                    ->where('deleted_at', NULL)->orderBy('id', 'DESC')->paginate($paginate);
                    // $data = 1;
                    // dd($data->links());
        return view('article.list', compact('data'));
    }


    public function developer($article_id)
    {
        $article = Article::with(['developer'=>function($q){ $q->where('deleted_at', null);}])->where('id', $article_id)->where('deleted_at', null)->first();
        // return $article;
        return view('article.developer', compact('article'));
    }

    public function developerStore(Request $request, $article_id)
    {
        // return $request;
        DB::beginTransaction();
        try {
            
            ArticlesDeveloper::create([
                'article_id'=> $article_id,
                'title'=>$request->title,
                'tool'=>$request->tool,
                'type'=>$request->type,
                'detail'=>$request->detail,
                'concatenar'=>$request->concatenar,
                'required'=>$request->required
            ]);
            DB::commit();
            return redirect()->route('articles.developer', ['article_id'=>$article_id])->with(['message' => 'Registrado exitosamente exitosamente.', 'alert-type' => 'success']);            
        } catch (\Throwable $th) {
            DB::rollBack();
            // return 0;
            return redirect()->route('articles.developer', ['article_id'=>$article_id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function developerDestroy($article_id, $detail_id)
    {
        DB::beginTransaction();
        try {
            // return $detail_id;
            ArticlesDeveloper::where('id', $detail_id)->update(['deleted_at'=>Carbon::now()]);
            DB::commit();
            return redirect()->route('articles.developer', ['article_id'=>$article_id])->with(['message' => 'Registrado exitosamente exitosamente.', 'alert-type' => 'success']);            
        } catch (\Throwable $th) {
            DB::rollBack();
            // return 0;
            return redirect()->route('articles.developer', ['article_id'=>$article_id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }




    //para la realizacion  de prestamos
    

    public function ajaxModel()
    {
        return ModelGarment::where('deleted_at', null)->get();
    }

    public function ajaxMarca()
    {
        return BrandGarment::where('deleted_at', null)->get();
    }


    public function ajaxDeveloper($article_id)
    {
        return ArticlesDeveloper::where('article_id', $article_id)->where('deleted_at', null)->get();
    }

}
