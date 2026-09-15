(() => {
    'use strict';
    const root = document.getElementById('guideTwo');
    const locations = window.saxGuideDirectory || [];
    if (!root || !locations.length) return;
    const BRAND_LIMIT = 6;
    const labels = window.saxGuideLabels;
    const get = id => root.querySelector('#guideTwo' + id);
    const norm = value => String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase().trim();
    const node = (tag, text, className) => {
        const element = document.createElement(tag);
        if (text != null) element.textContent = text;
        if (className) element.className = className;
        return element;
    };
    let location = locations[0];
    let floorSections = [];
    let scrollFrame = null;
    let expandedGroups = new Set();
    let searchExpansionSnapshot = null;
    const storefrontHeader = document.querySelector('.sax-header, .vista-header');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const floorOrder = item => {
        const name = norm(item.searchName || item.name);
        if (/\b(pb|terreo|ground)\b/.test(name)) return 0;
        const number = name.match(/-?\d+/);
        return number ? Number(number[0]) : Infinity;
    };
    const orderedFloors = () => [...location.floors].sort((a, b) => floorOrder(a) - floorOrder(b));
    function headerOffset() {
        const height = storefrontHeader?.getBoundingClientRect().height || 0;
        return Math.ceil(height) + 24;
    }
    function updateIndex() {
        scrollFrame = null;
        const offset = headerOffset();
        root.style.setProperty('--guide-scroll-offset', offset + 'px');
        if (!floorSections.length) return;
        let current = floorSections[0];
        for (const section of floorSections) {
            if (section.getBoundingClientRect().top <= offset + 32) current = section;
        }
        // A short final floor may never reach the reading line at the document end.
        if (window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 2) {
            current = floorSections[floorSections.length - 1];
        }
        get('Floor').value = current.id;
        get('Floors').querySelectorAll('a').forEach(link => {
            if (link.hash === '#' + current.id) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
        });
    }
    function scheduleIndex() {
        if (scrollFrame === null) scrollFrame = requestAnimationFrame(updateIndex);
    }
    function navigateTo(id, updateHistory = true) {
        const target = document.getElementById(id);
        if (!target || !get('Content').contains(target)) return;
        root.style.setProperty('--guide-scroll-offset', headerOffset() + 'px');
        if (updateHistory && window.location.hash !== '#' + id) {
            history.pushState(null, '', '#' + id);
        }
        target.focus({preventScroll: true});
        target.scrollIntoView({block: 'start', behavior: reducedMotion.matches ? 'instant' : 'smooth'});
        scheduleIndex();
    }
    const contact = data => {
        const link = node('a', null, 'guide-two__contact');
        link.href = data.url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        const icon = node('i', null, 'fa-brands fa-whatsapp');
        icon.setAttribute('aria-hidden', 'true');
        link.append(icon, document.createTextNode(labels.contact));
        return link;
    };
    function setGroupExpanded(section, expanded, preserveTogglePosition = false) {
        const toggle = section.querySelector('[data-guide-brand-toggle]');
        if (!toggle) return;
        const previousTop = preserveTogglePosition ? toggle.getBoundingClientRect().top : null;
        section.querySelectorAll('[data-guide-brand]').forEach((brand, index) => {
            brand.hidden = !expanded && index >= BRAND_LIMIT;
        });
        toggle.setAttribute('aria-expanded', String(expanded));
        toggle.textContent = expanded
            ? labels.see_less
            : labels.see_more.replace(':count', section.dataset.hiddenBrands);
        if (preserveTogglePosition) {
            const movement = toggle.getBoundingClientRect().top - previousTop;
            if (movement) window.scrollBy({top: movement, behavior: 'instant'});
            toggle.focus({preventScroll: true});
        }
        scheduleIndex();
    }
    function refreshExpandedGroups() {
        get('Content').querySelectorAll('[data-guide-group]').forEach(section => {
            setGroupExpanded(section, expandedGroups.has(section.dataset.guideGroup));
        });
    }
    function revealTarget(id, temporary) {
        const target = document.getElementById(id);
        if (!target?.hidden) return;
        const section = target.closest('[data-guide-group]');
        if (!section) return;
        if (temporary && searchExpansionSnapshot === null) {
            searchExpansionSnapshot = new Set(expandedGroups);
        }
        expandedGroups.add(section.dataset.guideGroup);
        setGroupExpanded(section, true);
    }
    function renderFloor(floor) {
        const content = node('section', null, 'guide-two__floor');
        content.id = floor.id;
        content.tabIndex = -1;
        content.setAttribute('aria-labelledby', floor.id + '-title');
        const header = node('header', null, 'guide-two__floor-head');
        const heading = node('h2', floor.name);
        heading.id = floor.id + '-title';
        header.append(heading, node('p', floor.groups.map(group => group.name).join(' · ')));
        const groups = node('div', null, 'guide-two__groups');
        groups.dataset.count = floor.groups.length;
        floor.groups.forEach(group => {
            const section = node('section', null, 'guide-two__group');
            section.id = group.id;
            section.tabIndex = -1;
            section.dataset.guideGroup = group.id;
            const title = node('h3', group.name);
            title.id = group.id + '-title';
            section.setAttribute('aria-labelledby', title.id);
            section.append(title);
            if (group.description) section.append(node('p', group.description));
            const brands = node('ul', null, 'guide-two__brands');
            brands.id = group.id + '-brands';
            group.brands.forEach((brand, index) => {
                const item = node('li', brand);
                item.id = group.id + '-brand-' + index;
                item.tabIndex = -1;
                item.dataset.guideBrand = '';
                item.hidden = index >= BRAND_LIMIT && !expandedGroups.has(group.id);
                brands.append(item);
            });
            if (group.brands.length) section.append(brands);
            if (group.brands.length > BRAND_LIMIT) {
                section.dataset.hiddenBrands = String(group.brands.length - BRAND_LIMIT);
                const toggle = node('button', null, 'guide-two__brands-toggle');
                toggle.type = 'button';
                toggle.dataset.guideBrandToggle = '';
                toggle.setAttribute('aria-controls', brands.id);
                toggle.addEventListener('click', () => {
                    const expanded = !expandedGroups.has(group.id);
                    if (expanded) expandedGroups.add(group.id);
                    else expandedGroups.delete(group.id);
                    setGroupExpanded(section, expanded, !expanded);
                });
                section.append(toggle);
                setGroupExpanded(section, expandedGroups.has(group.id));
            }
            group.contacts.forEach(data => section.append(contact(data)));
            groups.append(section);
        });
        content.append(header, groups);
        if (floor.contacts.length) {
            const shared = node('div', null, 'guide-two__shared');
            shared.append(node('h3', labels.contacts));
            floor.contacts.forEach(data => shared.append(node('p', data.label), contact(data)));
            content.append(shared);
        }
        return content;
    }
    function selectLocation() {
        expandedGroups = new Set();
        searchExpansionSnapshot = null;
        const hero = get('Hero');
        hero.classList.toggle('has-image', Boolean(location.heroImage));
        hero.style.backgroundImage = location.heroImage ? `url("${location.heroImage.replace(/["\\]/g, '\\$&')}")` : '';
        get('Floor').replaceChildren();
        get('Floors').replaceChildren();
        get('Content').replaceChildren();
        get('Hours').textContent = location.hours || '';
        orderedFloors().forEach(item => {
            const option = node('option', item.name);
            option.value = item.id;
            get('Floor').append(option);
            const link = node('a', item.name);
            link.href = '#' + item.id;
            link.addEventListener('click', event => {
                if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                event.preventDefault();
                navigateTo(item.id);
            });
            get('Floors').append(link);
            get('Content').append(renderFloor(item));
        });
        floorSections = [...get('Content').children];
        search();
        updateIndex();
    }
    function search() {
        const term = norm(get('Search').value);
        if (!term && searchExpansionSnapshot !== null) {
            expandedGroups = new Set(searchExpansionSnapshot);
            searchExpansionSnapshot = null;
            refreshExpandedGroups();
        }
        get('Clear').hidden = !get('Search').value;
        get('Results').hidden = !term;
        const list = get('ResultList');
        list.replaceChildren();
        if (!term) return;
        const results = [];
        location.floors.forEach(item => {
            if (norm(item.name + ' ' + item.searchName).includes(term)) {
                results.push({floor: item, title: item.name, context: location.name, target: item.id});
            }
            item.groups.forEach(group => {
                if (norm(group.name).includes(term)) results.push({floor: item, title: group.name, context: item.name, target: group.id});
                group.brands.forEach((brand, index) => {
                    if (norm(brand).includes(term)) results.push({floor: item, group, brandIndex: index, title: brand, context: group.name + ' · ' + item.name, target: group.id + '-brand-' + index});
                });
            });
        });
        get('ResultStatus').textContent = results.length ? labels.results_count.replace(':count', results.length) : labels.empty;
        results.forEach(result => {
            const li = node('li');
            const button = node('button');
            button.type = 'button';
            button.append(node('strong', result.title), node('span', result.context));
            button.addEventListener('click', () => {
                if (result.group && result.brandIndex >= BRAND_LIMIT) revealTarget(result.target, true);
                navigateTo(result.target);
            });
            li.append(button);
            list.append(li);
        });
    }
    get('Location').addEventListener('change', event => {
        location = locations.find(item => item.id === event.target.value) || locations[0];
        history.replaceState(null, '', window.location.pathname + window.location.search);
        selectLocation();
    });
    get('Floor').addEventListener('change', event => {
        navigateTo(event.target.value);
    });
    get('Search').addEventListener('input', search);
    get('Clear').addEventListener('click', () => { get('Search').value = ''; search(); get('Search').focus(); });
    get('Location').value = location.id;
    function followHash() {
        const id = window.location.hash.slice(1);
        const destination = locations.find(item => item.floors.some(floor =>
            floor.id === id || floor.groups.some(group => group.id === id || group.brands.some((brand, index) => group.id + '-brand-' + index === id))));
        if (!destination) return;
        if (location !== destination) {
            location = destination;
            get('Location').value = location.id;
            selectLocation();
        }
        revealTarget(id, false);
        navigateTo(id, false);
    }
    selectLocation();
    window.addEventListener('scroll', scheduleIndex, {passive: true});
    window.addEventListener('resize', scheduleIndex);
    window.addEventListener('hashchange', followHash);
    if (storefrontHeader) new ResizeObserver(scheduleIndex).observe(storefrontHeader);
    new ResizeObserver(scheduleIndex).observe(get('Content'));
    followHash();
})();
