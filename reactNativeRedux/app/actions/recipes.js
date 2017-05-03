import * as types from './types';
import Api from '../lib/api';

export function fetchRecipes(ingredients) {
	return (dispatch, getState) => {
		const params = [
			`ingredients=${encodeURIComponent(ingredients)}`,
			'fillIngredients=false',
			'limitLicense=false',
			'number=20',
			'ranking=1'
		].join('&');

		return Api.get(`/recipes/findByIngredients?${params}`)
			.then(response => {
				dispatch(setSearchedRecipes({recipes: response}));
			})
			.catch(error => {
				console.log(error);
			});
	};
}

export function setSearchedRecipes({ recipes }) {
	return {
		type: types.SET_SEARCHED_RECIPES,
		recipes
	};
}

export function addRecipe() {
	return {
		type: types.ADD_RECIPE
	};
}