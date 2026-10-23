(() => {
    'use strict';
    const root = document.getElementById('guideTwo');
    const locations = window.saxGuideDirectory || [];
    if (!root || !locations.length) return;
    const BRAND_LIMIT = 6;
    const RESULT_LIMIT = 12;
    const labels = window.saxGuideLabels;
    const get = id => root.querySelector('#guideTwo' + id);
    const norm = value => String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLocaleLowerCase()
        .replace(/[^a-z0-9]+/g, ' ')
        .trim()
        .replace(/\s+/g, ' ');
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
    let activeSearchResult = -1;
    let searchFrame = null;
    const storefrontHeader = document.querySelector('.sax-header, .vista-header');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const floorOrder = item => {
        const name = norm(item.searchName || item.name);
        if (/\b(pb|terreo|ground)\b/.test(name)) return 0;
        const number = name.match(/-?\d+/);
        return number ? Number(number[0]) : Infinity;
    };
    const orderedFloors = () => [...location.floors].sort((a, b) => floorOrder(a) - floorOrder(b));
    const editDistance = (left, right, limit = 2) => {
        if (Math.abs(left.length - right.length) > limit) return limit + 1;
        let previousPrevious = null;
        let previous = Array.from({length: right.length + 1}, (_, index) => index);
        for (let row = 1; row <= left.length; row++) {
            const current = [row];
            let rowBest = current[0];
            for (let column = 1; column <= right.length; column++) {
                const cost = left[row - 1] === right[column - 1] ? 0 : 1;
                current[column] = Math.min(
                    current[column - 1] + 1,
                    previous[column] + 1,
                    previous[column - 1] + cost,
                );
                if (previousPrevious && row > 1 && column > 1
                    && left[row - 1] === right[column - 2]
                    && left[row - 2] === right[column - 1]) {
                    current[column] = Math.min(current[column], previousPrevious[column - 2] + 1);
                }
                rowBest = Math.min(rowBest, current[column]);
            }
            if (rowBest > limit) return limit + 1;
            previousPrevious = previous;
            previous = current;
        }
        return previous[right.length];
    };
    const tokenScore = (queryToken, candidateToken) => {
        if (queryToken === candidateToken) return 100;
        if (candidateToken.startsWith(queryToken)) return 92;
        if (queryToken.length >= 3 && candidateToken.includes(queryToken)) return 84;
        if (queryToken.length < 3 || candidateToken.length < 3) return 0;
        const limit = Math.max(queryToken.length, candidateToken.length) >= 7 ? 2 : 1;
        const distance = editDistance(queryToken, candidateToken, limit);
        return distance <= limit ? 76 - (distance * 9) : 0;
    };
    const searchScore = (query, values) => {
        const normalizedValues = values.map(norm).filter(Boolean);
        const joined = normalizedValues.join(' ');
        if (!joined) return 0;
        if (normalizedValues.some(value => value === query)) return 1000;
        if (normalizedValues.some(value => value.startsWith(query))) return 920;
        if (normalizedValues.some(value => value.split(' ').some(word => word.startsWith(query)))) return 880;
        if (normalizedValues.some(value => value.includes(query))) return 820;
        const queryTokens = query.split(' ').filter(Boolean);
        const candidateTokens = joined.split(' ').filter(Boolean);
        let total = 0;
        for (const queryToken of queryTokens) {
            const best = candidateTokens.reduce((score, candidateToken) => Math.max(score, tokenScore(queryToken, candidateToken)), 0);
            if (!best) return 0;
            total += best;
        }
        return Math.round((total / queryTokens.length) * 7);
    };
    const searchIndex = locations.flatMap(destination => destination.floors.flatMap(floor => {
        const locationTerms = [destination.name, destination.city, ...(destination.searchTerms || [])];
        const floorResult = {
            kind: 'floor', location: destination, floor, title: floor.name,
            context: destination.name + ' — ' + destination.city,
            target: floor.id,
            terms: [floor.name, floor.searchName, ...(floor.searchTerms || []), ...locationTerms],
        };
        const groupResults = floor.groups.flatMap(group => {
            const groupResult = {
                kind: 'group', location: destination, floor, group, title: group.name,
                context: floor.name + ' · ' + destination.name,
                target: group.id,
                terms: [group.name, group.description, ...(group.searchTerms || []), floor.name, ...locationTerms],
            };
            const brandResults = group.brands.map((brand, brandIndex) => ({
                kind: 'brand', location: destination, floor, group, brandIndex, title: brand,
                context: group.name + ' · ' + floor.name + ' · ' + destination.name,
                target: group.id + '-brand-' + brandIndex,
                terms: [brand],
            }));
            return [groupResult, ...brandResults];
        });
        return [floorResult, ...groupResults];
    }));
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
    function setActiveSearchResult(index) {
        const options = [...get('ResultList').querySelectorAll('[role="option"]')];
        if (!options.length) {
            activeSearchResult = -1;
            get('Search').removeAttribute('aria-activedescendant');
            return;
        }
        activeSearchResult = (index + options.length) % options.length;
        options.forEach((option, optionIndex) => option.setAttribute('aria-selected', String(optionIndex === activeSearchResult)));
        const active = options[activeSearchResult];
        get('Search').setAttribute('aria-activedescendant', active.id);
        active.scrollIntoView({block: 'nearest'});
    }
    function activateSearchResult(result) {
        if (location.id !== result.location.id) {
            location = result.location;
            get('Location').value = location.id;
            history.replaceState(null, '', window.location.pathname + window.location.search);
            selectLocation();
        }
        if (result.kind === 'brand' && result.brandIndex >= BRAND_LIMIT) revealTarget(result.target, true);
        navigateTo(result.target);
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
        get('Search').setAttribute('aria-expanded', String(Boolean(term)));
        get('Search').removeAttribute('aria-activedescendant');
        activeSearchResult = -1;
        const list = get('ResultList');
        list.replaceChildren();
        if (!term) return;
        const results = searchIndex
            .map(result => ({...result, score: searchScore(term, result.terms) + (result.location.id === location.id ? 24 : 0)}))
            .filter(result => result.score > 0)
            .sort((left, right) => right.score - left.score || left.title.localeCompare(right.title));
        get('ResultStatus').textContent = results.length ? labels.results_count.replace(':count', results.length) : labels.empty;
        results.slice(0, RESULT_LIMIT).forEach((result, index) => {
            const li = node('li');
            li.setAttribute('role', 'none');
            const button = node('button');
            button.type = 'button';
            button.id = 'guideTwoResult-' + index;
            button.setAttribute('role', 'option');
            button.setAttribute('aria-selected', 'false');
            const heading = node('strong', result.title);
            heading.prepend(node('i', null, result.kind === 'brand' ? 'fa-solid fa-tag' : (result.kind === 'group' ? 'fa-solid fa-location-dot' : 'fa-solid fa-layer-group')));
            button.append(heading, node('span', result.context));
            button.addEventListener('mouseenter', () => setActiveSearchResult(index));
            button.addEventListener('click', () => activateSearchResult(result));
            li.append(button);
            list.append(li);
        });
    }
    function scheduleSearch() {
        if (searchFrame !== null) cancelAnimationFrame(searchFrame);
        searchFrame = requestAnimationFrame(() => {
            searchFrame = null;
            search();
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
    get('Search').addEventListener('input', scheduleSearch);
    get('Search').addEventListener('keydown', event => {
        const options = get('ResultList').querySelectorAll('[role="option"]');
        if (event.key === 'ArrowDown' && options.length) {
            event.preventDefault();
            setActiveSearchResult(activeSearchResult + 1);
        } else if (event.key === 'ArrowUp' && options.length) {
            event.preventDefault();
            setActiveSearchResult(activeSearchResult < 0 ? options.length - 1 : activeSearchResult - 1);
        } else if (event.key === 'Enter' && activeSearchResult >= 0) {
            event.preventDefault();
            options[activeSearchResult].click();
        } else if (event.key === 'Escape' && get('Search').value) {
            get('Search').value = '';
            search();
        }
    });
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
