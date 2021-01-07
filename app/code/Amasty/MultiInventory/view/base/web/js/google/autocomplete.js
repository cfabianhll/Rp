define([
    'uiRegistry',
    'underscore'
], function (reg, _) {
    return {
        componentForm: {
            locality: null,
            postal_code: null,
            country: null,
            administrative_area_level_1: null
        },

        componentNames: {
            street_number: 'short_name',
            route: 'long_name',
            administrative_area_level_1: 'long_name',
            country: 'short_name',
            locality: 'long_name',
            postal_code: 'short_name'
        },

        autocomplete: null,

        resultData: null,

        regionUpdate: 0,

        fieldAddress: null,

        init: function (id) {
            this.autocomplete = new google.maps.places.Autocomplete(
                (document.getElementById(id)),
                {types: ['geocode']}
            );
            this.autocomplete.addListener('place_changed', this.fillInAddress.bind(this));
        },

        fillInAddress: function () {
            var place = this.autocomplete.getPlace();
            var local = {};
            this.fieldAddress.value(place.formatted_address);
            for (var i = 0; i < place.address_components.length; i++) {
                var addressType = place.address_components[i].types[0];
                if (this.componentForm[addressType]) {
                    var val = place.address_components[i][this.componentNames[addressType]];
                    if (addressType == 'country' || addressType == 'administrative_area_level_1') {
                        local[addressType] = {'object': this.componentForm[addressType], 'value': val};
                    } else {
                        this.componentForm[addressType].value(val);
                    }
                }
            }
            if (_.size(local) > 0) {
                this.updateRegion(local);
            }
        },

        setComponents: function (object) {
            this.componentForm = object;
        },

        updateRegion: function (object) {
            if ('country' in object) {
                var country = object['country']['object'];
                country.value(object['country']['value']);
                if ('administrative_area_level_1' in object) {
                    this.changeState(object);
                }
            }
        },

        setAddress: function (object) {
            this.fieldAddress = object;
        },

        changeState: function (object) {
            var state = object['administrative_area_level_1']['object'];
            var stateId = reg.get(state.parentName + '.' + state.inputName + "_id");

            if (stateId.options().length == 0) {
                state.value(object['administrative_area_level_1']['value']);
            } else {
                var option = stateId.options().find(function (option) {
                    return option.label == object['administrative_area_level_1']['value'];
                });

                if (option) {
                    stateId.value(option.value);
                }
            }
        }
    }
});
