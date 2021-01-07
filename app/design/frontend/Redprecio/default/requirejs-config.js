var config = {
    paths: {
            'bootstrap':'js/bootstrap.min',
            'popper':'js/popper.min',
            'owl':'js/owl.carousel.min',
			'scroll':'js/jquery.slimscroll.min',
			'wow':'js/wow.min'
    } ,
    shim: {
        'bootstrap': {
            'deps': ['jquery']
        },
		'popper': {
            'deps': ['jquery']
        },
        'owl': {
            'deps': ['jquery']
        },
		'scroll': {
            'deps': ['jquery']
        },
		'wow': {
            'deps': ['jquery']
        }
    }
};