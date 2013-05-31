var ValuesDiffs = (function() {

	var markup = '';
	var tname = '';

	var get = function(d1, d2, tableName) {

		tname = tableName;
		markup = '';

		// checking of the table exists in both places
		if(!d1.tables[tname] || !d2.tables[tname]) {
			markup = 'The table ' + tname + ' is missing in some of the databases.';
		} else {
			var diff = objectDiff.diffOwnProperties(d1.tables[tname].values, d2.tables[tname].values);
			markup = '<pre>' + objectDiff.convertToXMLString(diff) + '</pre>';
		}

		return markup;
	}

	return {
		get: get
	}

})();