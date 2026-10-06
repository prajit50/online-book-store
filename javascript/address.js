(function () {
    const addressBase = new URL('../includes/address/', document.currentScript.src);

    function getSelect(selector) {
        return document.getElementById(selector) || document.querySelector(`[data-address="${selector}"]`);
    }

    function parseJson(response) {
        if (!response.ok) {
            throw new Error(`Failed to load address data (${response.status})`);
        }

        return response.json();
    }

    function populateSelect(select, items, placeholder, selectedValue) {
        if (!select) {
            return;
        }

        const options = [{ value: '', text: placeholder, id: '' }];

        items.forEach((item) => {
            options.push({
                value: String(item.name),
                text: item.name,
                id: String(item.id),
            });
        });

        select.innerHTML = options
            .map((option) => `<option value="${option.value}" data-id="${option.id}">${option.text}</option>`)
            .join('');

        select.disabled = items.length === 0;

        if (selectedValue) {
            select.value = String(selectedValue);
        }
    }

    function populateWardSelect(select, wardCount, placeholder) {
        if (!select) {
            return;
        }

        const totalWards = Number(wardCount) || 0;
        const options = [{ value: '', text: placeholder }];

        for (let ward = 1; ward <= totalWards; ward += 1) {
            options.push({
                value: String(ward),
                text: `Ward ${ward}`,
            });
        }

        select.innerHTML = options
            .map((option) => `<option value="${option.value}">${option.text}</option>`)
            .join('');

        select.disabled = totalWards === 0;
    }

    function getWardCount(item) {
        const rawValue = item.wards ?? item.num_wards ?? item.ward_count ?? 0;
        const count = Number(rawValue);

        return Number.isFinite(count) && count > 0 ? count : 0;
    }

    function initAddressFields() {
        const provinceSelect = getSelect('province');
        const districtSelect = getSelect('district');
        const municipalitySelect = getSelect('municipality');
        const wardSelect = getSelect('ward');

        if (!provinceSelect && !districtSelect && !municipalitySelect && !wardSelect) {
            return;
        }

        const province = provinceSelect || document.createElement('select');
        const district = districtSelect || document.createElement('select');
        const municipality = municipalitySelect || document.createElement('select');
        const ward = wardSelect || document.createElement('select');

        const loadAddressData = (file) => fetch(new URL(file, addressBase), { cache: 'no-store' }).then(parseJson);

        Promise.all([
            loadAddressData('provinces.json'),
            loadAddressData('districts.json'),
            loadAddressData('municipalities.json'),
        ])
            .then(([provinces, districts, municipalities]) => {
                populateSelect(province, provinces, 'Select province', '');

                province.addEventListener('change', function () {
                    const selectedProvince = provinces.find((provinceItem) => provinceItem.name === this.value);
                    const provinceId = selectedProvince ? Number(selectedProvince.id) : null;
                    const filteredDistricts = districts.filter((districtItem) => districtItem.province_id === provinceId);

                    populateSelect(district, filteredDistricts, 'Select district', '');
                    populateSelect(municipality, [], 'Select municipality', '');
                    populateWardSelect(ward, 0, 'Select ward');
                    municipality.disabled = true;
                    ward.disabled = true;
                });

                district.addEventListener('change', function () {
                    const selectedDistrict = districts.find((districtItem) => districtItem.name === this.value);
                    const districtId = selectedDistrict ? Number(selectedDistrict.id) : null;
                    const filteredMunicipalities = municipalities.filter((municipalityItem) => municipalityItem.district_id === districtId);

                    populateSelect(municipality, filteredMunicipalities, 'Select municipality', '');
                    populateWardSelect(ward, 0, 'Select ward');
                    ward.disabled = true;
                });

                municipality.addEventListener('change', function () {
                    const selectedMunicipality = municipalities.find((municipalityItem) => municipalityItem.name === this.value);
                    const municipalityId = selectedMunicipality ? Number(selectedMunicipality.id) : null;
                    const wardCount = municipalityId !== null ? getWardCount(municipalities.find((item) => item.id === municipalityId) || {}) : 0;

                    populateWardSelect(ward, wardCount, 'Select ward');
                });

                district.disabled = true;
                municipality.disabled = true;
                ward.disabled = true;
            })
            .catch((error) => {
                console.error('Address data could not be loaded:', error);

                if (province) {
                    province.innerHTML = '<option value="">Province unavailable</option>';
                    province.disabled = true;
                }

                if (district) {
                    district.innerHTML = '<option value="">District unavailable</option>';
                    district.disabled = true;
                }

                if (municipality) {
                    municipality.innerHTML = '<option value="">Municipality unavailable</option>';
                    municipality.disabled = true;
                }

                if (ward) {
                    ward.innerHTML = '<option value="">Ward unavailable</option>';
                    ward.disabled = true;
                }
            });
    }

    document.addEventListener('DOMContentLoaded', initAddressFields);
})();
