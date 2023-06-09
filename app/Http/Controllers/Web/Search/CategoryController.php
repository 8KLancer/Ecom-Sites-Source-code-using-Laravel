<?php

namespace App\Http\Controllers\Web\Search;

use App\Helpers\Search\PostQueries;
use Illuminate\Support\Str;
use Torann\LaravelMetaTags\Facades\MetaTag;

class CategoryController extends BaseController
{
	public $isCatSearch = true;
	
	/**
	 * Category URL
	 * Pattern: (countryCode/)category/category-slug
	 * Pattern: (countryCode/)category/parent-category-slug/category-slug
	 *
	 * @param $countryCode
	 * @param $catSlug
	 * @param null $subCatSlug
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
	 */
	public function index($countryCode, $catSlug, $subCatSlug = null)
	{

        // Check if Category is found
		if (empty($this->cat)) {
			abort(404, t('category_not_found'));
		}
		
		// Search
		$data = (new PostQueries($this->preSearch))->fetch();
		// Get Titles
		$bcTab = $this->getBreadcrumb();
		$htmlTitle = $this->getHtmlTitle();
		view()->share('bcTab', $bcTab);
		view()->share('htmlTitle', $htmlTitle);
		
		// SEO
		$title = ''. $this->cat->name . ' a venda em Angola - Paiaki';
		$keywords = 'comprar, vender, alugar '.$this->cat->name.', paiaki, online, angola, anúncios classificados, bom preço, barato, site de compra e venda, negocio angola, business';
		if (isset($this->cat->description) && !empty($this->cat->description)) {
			$description = Str::limit($this->cat->description, 200);
		} else {
			$description = Str::limit(t('ads_category_in_location', [
					'category' => $this->cat->name,
					'location' => config('country.name'),
				]) . '. ' . 'Procurando por ' . $this->cat->name . ' a venda para comprar em Luanda ou em outra cidade de Angola? Encontre vários anúncios no Paiaki Angola', 200);
		}
		
		// Meta Tags
		MetaTag::set('title', $title);
		MetaTag::set('description', $description);
		MetaTag::set('keywords', $keywords);
		
		// Open Graph
		$this->og->title($title)->description($description)->type('website');
		$this->og->image(asset('images/Bannercover.png'));
		
		view()->share('og', $this->og);
		
		return appView('search.results', $data);
	}
}
