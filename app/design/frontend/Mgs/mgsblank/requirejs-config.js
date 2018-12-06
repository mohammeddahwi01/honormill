var config = {
    map: {
        '*': {
			html5shiv: 'js/html5shiv',
			responsive: 'js/responsive',
			theme: 'js/theme'
        }
    },
    paths:  {
        "scrollfix" : "Magento_Theme/js/jquery-scrolltofixed-min"   
    },
    "shim": {
        "scrollfix": {
             deps: ['jquery']
        },
	}
};