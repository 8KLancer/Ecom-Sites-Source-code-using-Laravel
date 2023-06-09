<?php
/**
 * LaraClassified - Classified Ads Web Application
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: https://bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from CodeCanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace App\Http\Controllers\Web\Search\Traits;

use App\Helpers\Search\PostQueries;
use App\Helpers\UrlGen;
use App\Http\Controllers\Web\Post\Traits\CatBreadcrumbTrait;
use Illuminate\Support\Arr;

trait TitleTrait
{
	use CatBreadcrumbTrait;
	
	/**
	 * Get Search Title
	 *
	 * @return string
	 */
	public function getTitle()
	{
		$title = '';
		
		// Init.
		$title .= t('free_ads');
		
		// Keyword
		if (request()->filled('q')) {
			$title .= ' ' . 'de' . ' ';
			$title .= '' . rawurldecode(request()->get('q')) . '';
		}
		
		// Category
		if (isset($this->cat) && !empty($this->cat)) {
			// SubCategory
			if (isset($this->isSubCatSearch) && $this->isSubCatSearch) {
				if (isset($this->subCat) && !empty($this->subCat)) {
					$title .= ' ' . $this->subCat->name . ',';
				}
			}
			
			$title .= ' ' . $this->cat->name;
		}
		
		// User
		if (isset($this->sUser) && !empty($this->sUser)) {
			$title .= ' ' . t('of') . ' ';
			$title .= $this->sUser->name;
		}
		
		// Tag
		if (isset($this->tag) && !empty($this->tag)) {
			$title .= ' ' . t('for') . ' ';
			$title .= $this->tag . ' (' . t('Tag') . ')';
		}
		
		// Location
		if (request()->filled('r') && !request()->filled('l')) {
			// Administrative Division
			if (isset($this->admin) && !empty($this->admin)) {
				$title .= ' ' . t('in') . ' ';
				$title .= $this->admin->name;
			}
		} else {
			// City
			if (isset($this->city) && !empty($this->city)) {
				$title .= ' ' . t('in') . ' ';
				$title .= $this->city->name;
			}
		}
		
		// Country
		$title .= ', ' . config('country.name');
		
		view()->share('title', $title);
		
		return $title;
	}
	
	/**
	 * Get Search HTML Title
	 *
	 * @return string
	 */
	public function getHtmlTitle()
	{
		// Title
		$htmlTitle = '';
		
		// Init.
		$attr = ['countryCode' => config('country.icode')];
		$htmlTitle .= '<a href="' . UrlGen::search() . '" class="current">';
		$htmlTitle .= '<span>' . t('All ads') . '</span>';
		$htmlTitle .= '</a>';
		
		// Location
		$searchUrl = UrlGen::search([], ['l', 'r', 'location', 'distance']);
		
		if (request()->filled('r') && !request()->filled('l')) {
			// Administrative Division
			if (isset($this->admin) && !empty($this->admin)) {
				$htmlTitle .= ' ' . t('in') . ' ';
				$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . $searchUrl . '">';
				$htmlTitle .= $this->admin->name;
				$htmlTitle .= '</a>';
			}
		} else {
			// City
			if (isset($this->city) && !empty($this->city)) {
				if (config('settings.listing.cities_extended_searches')) {
					$htmlTitle .= ' ' . t('within') . ' ';
					$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . $searchUrl . '">';
					$htmlTitle .= t('x_distance_around_city', [
						'distance' => (PostQueries::$distance == 1) ? 0 : PostQueries::$distance,
						'unit'     => getDistanceUnit(config('country.code')),
						'city'     => $this->city->name]);
					$htmlTitle .= '</a>';
				} else {
					$htmlTitle .= ' ' . t('in') . ' ';
					$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . $searchUrl . '">';
					$htmlTitle .= $this->city->name;
					$htmlTitle .= '</a>';
				}
			}
		}
		
		// Category
		if (isset($this->cat) && !empty($this->cat)) {
			// Get the parent of parent category URL
			$exceptArr = ['c', 'sc', 'cf', 'minPrice', 'maxPrice'];
			$searchUrl = UrlGen::getCatParentUrl($this->cat->parent->parent ?? null, $this->city ?? null, $exceptArr);
			
			if (isset($this->cat->parent) && !empty($this->cat->parent)) {
				$htmlTitle .= ' ' . t('in') . ' ';
				$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . $searchUrl . '">';
				$htmlTitle .= $this->cat->parent->name;
				$htmlTitle .= '</a>';
				
				// Get the parent category URL
				$exceptArr = ['sc', 'cf', 'minPrice', 'maxPrice'];
				$searchUrl = UrlGen::getCatParentUrl($this->cat->parent ?? null, $this->city ?? null, $exceptArr);
			}
			
			$htmlTitle .= ' ' . t('in') . ' ';
			$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . $searchUrl . '">';
			$htmlTitle .= $this->cat->name;
			$htmlTitle .= '</a>';
		}
		
		// Tag
		if (isset($this->tag) && !empty($this->tag)) {
			$htmlTitle .= ' ' . t('for') . ' ';
			$attr = ['countryCode' => config('country.icode')];
			$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . UrlGen::search() . '">';
			$htmlTitle .= $this->tag;
			$htmlTitle .= '</a>';
		}
		
		// Date
		if (request()->filled('postedDate') && isset($this->dates) && isset($this->dates->{request()->get('postedDate')})) {
			$exceptArr = ['postedDate'];
			$searchUrl = UrlGen::search([], $exceptArr);
			
			$htmlTitle .= t('last');
			$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . $searchUrl . '">';
			$htmlTitle .= $this->dates->{request()->get('postedDate')};
			$htmlTitle .= '</a>';
		}
		
		// Condition
		if (request()->filled('new') && isset($this->conditions) && isset($this->conditions->{request()->get('new')})) {
			$exceptArr = ['new'];
			$searchUrl = UrlGen::search([], $exceptArr);
			
			$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . $searchUrl . '">';
			$htmlTitle .= $this->conditions->{request()->get('new')};
			$htmlTitle .= '</a>';
		}
		
		view()->share('htmlTitle', $htmlTitle);
		
		return $htmlTitle;
	}
	
	/**
	 * Get Breadcrumbs Tabs
	 *
	 * @return array
	 */
	public function getBreadcrumb()
	{
		$bcTab = [];
		
		// City
		if (isset($this->city) && !empty($this->city)) {
			$title = t('in_x_distance_around_city', [
				'distance' => (PostQueries::$distance == 1) ? 0 : PostQueries::$distance,
				'unit'     => getDistanceUnit(config('country.code')),
				'city'     => $this->city->name,
			]);
			
			$bcTab[] = collect([
				'name'     => (isset($this->cat) ? t('All ads') . ' ' . $title : $this->city->name),
				'url'      => UrlGen::city($this->city),
				'position' => (isset($this->cat) ? 5 : 3),
				'location' => true,
			]);
		}
		
		// Admin
		if (isset($this->admin) && !empty($this->admin)) {
			$queryArr = [
				'd' => config('country.icode'),
				'r' => $this->admin->name
			];
			$exceptArr = ['l', 'location', 'distance'];
			$searchUrl = UrlGen::search($queryArr, $exceptArr);
			
			$title = $this->admin->name;
			
			$attr = ['countryCode' => config('country.icode')];
			$bcTab[] = collect([
				'name'     => (isset($this->cat) ? t('All ads') . ' ' . $title : $this->admin->name),
				'url'      => $searchUrl,
				'position' => (isset($this->cat) ? 5 : 3),
				'location' => true,
			]);
		}
		
		// Category
		$catBreadcrumb = $this->getCatBreadcrumb($this->cat, 3);
		$bcTab = array_merge($bcTab, $catBreadcrumb);
		
		// Sort by Position
		$bcTab = array_values(Arr::sort($bcTab, function ($value) {
			return $value->get('position');
		}));
		
		view()->share('bcTab', $bcTab);
		
		return $bcTab;
	}
}
