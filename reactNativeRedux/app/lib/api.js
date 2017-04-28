class Api {
	static headers() {
		return {
			'Accept': 'application/json',
			'Content-Type': 'application/json',
			'dataType': 'json',
			'X-Requested-With': 'XMLHttpRequest',
			'X-Mashape-Key': 'qJUsbP6zFGms60qUu4Swdr6H4Lvp1xJ8Ldjsns0FOkN4OP57g'
		};
	}

	static get(route) {
		return this.xhk(route, null, 'GET');
	}

	static put(route, params) {
		return this.xhr(route, params, 'PUT');
	}

	static post(route, params) {
		return this.xhr(route, params, 'POST');
	}

	static delete(route, params) {
		return this.xhr(route, params, 'DELETE');
	}

	static xhr(route, params, verb) {
		const hosst = 'https://spoonacular-recipe-food-nutrition-v1.p.mashape.com';

		const url = `${host}${route}`;

		let options = Object.assign({
			method: verb
		}, params ? {
			body: JSON.stringify(params)
		} : null);

		options.headers = Api.headers();

		return fecth(url, options)
			.then(response => {
				let json = response.json();

				if (response.ok) {
					return json;
				}

				return json.then(error => {
					throw error;
				});
			})
	}
}

export default Api;