/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (function() { // webpackBootstrap
/*!***********************!*\
  !*** ./media/fapi.js ***!
  \***********************/
eval("// polyfill\r\nif (!Element.prototype.matches) {\r\n    Element.prototype.matches =\r\n        Element.prototype.msMatchesSelector ||\r\n        Element.prototype.webkitMatchesSelector;\r\n}\r\n\r\nif (!Element.prototype.closest) {\r\n    Element.prototype.closest = function(s) {\r\n        var el = this;\r\n\r\n        do {\r\n            if (Element.prototype.matches.call(el, s)) return el;\r\n            el = el.parentElement || el.parentNode;\r\n        } while (el !== null && el.nodeType === 1);\r\n        return null;\r\n    };\r\n}\r\n\r\ndocument.addEventListener('click', (event) => {\r\n    if (event.target.matches('.levels .remove')) {\r\n        let id = event.target.parentNode.getAttribute('data-id');\r\n        Swal.fire({\r\n            html: '<strong>Opravdu si přejete odstranit členskou sekci/úroveň?</strong><br><br>Smazáním sekce/úrovně nedojde ke smazání stránek v sekci/úrovni.',\r\n            showDenyButton: true,\r\n            confirmButtonText: `Smazat`,\r\n            denyButtonText: `Ponechat`,\r\n            customClass: {\r\n                confirmButton: 'removeConfirmButton',\r\n                denyButton: 'removeDenyButton',\r\n            }\r\n        }).then((result) => {\r\n            if (result.isConfirmed) {\r\n                let form = document.getElementById('LevelRemoveForm')\r\n                form.querySelector('[name=\"level_id\"]').setAttribute('value', id)\r\n                form.submit()\r\n            } else if (result.isDenied) {\r\n                // none\r\n            }\r\n        })\r\n    }\r\n})\r\n\r\ndocument.addEventListener('click', (event) => {\r\n    if (event.target.matches('.levels .edit')) {\r\n        let name = event.target.parentNode.querySelector('span').innerText;\r\n        let id = event.target.parentNode.getAttribute('data-id');\r\n        Swal.fire({\r\n            input: 'text',\r\n            inputLabel: 'Nový název',\r\n            inputValue: name,\r\n            showDenyButton: true,\r\n            confirmButtonText: `Přejmenovat`,\r\n            denyButtonText: `Ponechat`,\r\n            customClass: {\r\n                confirmButton: 'renameConfirmButton',\r\n                denyButton: 'renameDenyButton',\r\n            }\r\n\r\n        }).then((result) => {\r\n            if (result.isConfirmed) {\r\n                let form = document.getElementById('LevelEditForm')\r\n                form.querySelector('[name=\"level_id\"]').setAttribute('value', id)\r\n                form.querySelector('[name=\"name\"]').setAttribute('value', result.value)\r\n                form.submit()\r\n            } else if (result.isDenied) {\r\n                // none\r\n            }\r\n        })\r\n    }\r\n})\r\n\r\ndocument.addEventListener('click', (event) => {\r\n    if (event.target.matches('form.pages button')) {\r\n        event.preventDefault()\r\n        let id = findSelectedLevel()\r\n        let form = event.target.closest('form');\r\n        form.querySelector('[name=\"level_id\"]').value = id\r\n        form.submit()\r\n    }\r\n})\r\n\r\ndocument.addEventListener('click', (event) => {\r\n    if (event.target.matches('.levels a')) {\r\n        event.preventDefault()\r\n        let li = event.target.parentNode\r\n        Array.from(document.querySelectorAll('.levels li.selected')).forEach((one) => {\r\n            one.classList.remove('selected')\r\n        })\r\n        li.classList.add('selected')\r\n        reloadPagesToRemove()\r\n        recheckPagesToAdd()\r\n        reenableAddRemovePagesButton()\r\n        changeSubSubMenuLinks()\r\n    }\r\n})\r\n\r\ndocument.addEventListener('DOMContentLoaded', (event) => {\r\n\r\n    if (findSelectedLevel()) {\r\n        reloadPagesToRemove()\r\n    }\r\n    recheckPagesToAdd()\r\n    disableAddRemovePagesButton()\r\n    reenableAddRemovePagesButton()\r\n})\r\n\r\ndocument.addEventListener('click', (event) => {\r\n    if (event.target.matches('.oneEmail .carret') || event.target.matches('.oneEmail .header h3')) {\r\n        event.target.closest('.oneEmail').classList.toggle('open');\r\n    }\r\n})\r\n\r\ndocument.addEventListener('click', (event) => {\r\n    if (event.target.matches('.specifyLevelEmailCheckbox')) {\r\n        let label = event.target.closest('.oneEmail').querySelector('.body > p')\r\n        let subj = event.target.closest('.oneEmail').querySelector('#mail_subject')\r\n        let body = event.target.closest('.oneEmail').querySelector('#mail_body')\r\n        let inputs = event.target.closest('.oneEmail').querySelector('.inputs')\r\n        if(event.target.checked) {\r\n            label.classList.add('hidden')\r\n            subj.removeAttribute('readonly')\r\n            body.removeAttribute('readonly')\r\n            inputs.classList.remove('collapsed')\r\n        } else {\r\n            label.classList.remove('hidden')\r\n            subj.value = ''\r\n            body.value = ''\r\n            subj.setAttribute('readonly', true)\r\n            body.setAttribute('readonly', true)\r\n            inputs.classList.add('collapsed')\r\n        }\r\n    }\r\n})\r\n\r\ndocument.addEventListener('click', (event) => {\r\n    if (event.target.matches('.shortcodes h3') || event.target.matches('.shortcodes h3 .carret')) {\r\n        event.target.closest('.shortcodes').classList.toggle('open');\r\n    }\r\n})\r\n\r\nconst changeSubSubMenuLinks = () => {\r\n    let lvl = findSelectedLevel()\r\n    Array.from(document.querySelectorAll('.subsubmenuitem')).forEach((one) => {\r\n        let url = one.getAttribute('href')\r\n        let lvlR = new RegExp('&level=');\r\n        if (lvlR.test(url)) {\r\n            one.setAttribute(\r\n                'href',\r\n                url.replace(/(&level=[0-9]*)/, `&level=${lvl}`)\r\n                )\r\n        } else {\r\n            one.setAttribute('href', `${url}&level=${lvl}`)\r\n        }\r\n    })\r\n}\r\n\r\nconst levelToPages = (lvl) => {\r\n    if (!window.hasOwnProperty('LevelToPage')) {\r\n        let jsonEl = document.getElementById('LevelToPage');\r\n        if (jsonEl) {\r\n            window.LevelToPage = JSON.parse(jsonEl.innerText);\r\n        }\r\n    }\r\n    if (window.hasOwnProperty('LevelToPage')) {\r\n        return (window['LevelToPage'].hasOwnProperty(lvl)) ? window['LevelToPage'][lvl] : []\r\n    }\r\n    return []\r\n}\r\n\r\nconst disableAddRemovePagesButton = () => {\r\n    let r = document.querySelector('.removePagesForm .danger');\r\n    if (r) {\r\n        r.disabled = true\r\n    }\r\n    let a = document.querySelector('.addPagesForm .btn')\r\n    if (a) {\r\n        a.disabled = true\r\n    }\r\n}\r\n\r\nconst reenableAddRemovePagesButton = () => {\r\n    if (findSelectedLevel()) {\r\n        let r = document.querySelector('.removePagesForm .danger')\r\n        if (r && document.querySelector('.removePagesForm .onePage') !== null) {\r\n            r.disabled = false\r\n        }\r\n        let a = document.querySelector('.addPagesForm .btn')\r\n        if (a && document.querySelector('.addPagesForm .onePage') !== null) {\r\n            a.disabled = false\r\n        }\r\n    }\r\n}\r\n\r\nconst reloadPagesToRemove = () => {\r\n\r\n    let removeList = document.querySelector('.removePagesForm');\r\n    if (!removeList) {\r\n        return\r\n    }\r\n    let pages = levelToPages(findSelectedLevel())\r\n    let tail = pages.reduce((a, one) => {\r\n        return a + '&include[]=' + one;\r\n    }, '');\r\n    let inner = removeList.querySelector('.inner');\r\n    inner.innerHTML = ''\r\n    if (pages.length <= 0) {\r\n        inner.insertAdjacentHTML('afterbegin','<p>Sekce/úroveň nemá přiřazené stránky.</p>')\r\n        return\r\n    }\r\n    insertLoader(removeList)\r\n    fetch('/?rest_route=/wp/v2/pages&per_page=100&context=embed' + tail).then(res => res.json()).then(items => {\r\n        renderPagesForRemoval(inner, items)\r\n        removeLoader(removeList)\r\n        reenableAddRemovePagesButton()\r\n    });\r\n}\r\n\r\nconst recheckPagesToAdd = () => {\r\n\r\n    let addList = document.querySelector('.addPagesForm');\r\n    if (!addList) {\r\n        return\r\n    }\r\n    let disable = levelToPages(findSelectedLevel())\r\n    Array.from(addList.querySelectorAll('input[type=\"checkbox\"]')).forEach((one) => {\r\n        let id = parseInt(one.value)\r\n        if (disable.indexOf(id) >= 0) {\r\n            one.disabled = true\r\n        } else {\r\n            one.disabled = false\r\n        }\r\n    })\r\n\r\n}\r\n\r\nconst renderPagesForRemoval = (el, items) => {\r\n    let h = items.map((one) => {\r\n        return `<div class=\"onePage\"><input type=\"checkbox\" name=\"toRemove[]\" value=\"${one.id}\"> ${one.title.rendered}</div>`\r\n    })\r\n    el.innerHTML = ''\r\n    el.insertAdjacentHTML(\"beforeend\", h.join(''))\r\n    //el.insertAdjacentHTML(\"beforeend\", ``)\r\n\r\n}\r\n\r\nconst findSelectedLevel = () => {\r\n    let sLi = document.querySelector('.levels li.selected')\r\n    if (sLi) {\r\n        return parseInt(sLi.getAttribute('data-id'))\r\n    } else {\r\n        return null\r\n    }\r\n}\r\n\r\nconst insertLoader = (el) => {\r\n    el.classList.add('loading')\r\n}\r\n\r\nconst removeLoader = (el) => {\r\n    el.classList.remove('loading')\r\n}\r\n\r\nconst doHideMembershipUntil = () => {\r\n    let tables = document.querySelectorAll('.fapiMembership')\r\n    if (tables.length <= 0) {\r\n        return\r\n    }\r\n    Array.from(tables).forEach((table) => {\r\n        let unlimitedInputs = table.querySelectorAll('.isUnlimitedInput')\r\n        Array.from(unlimitedInputs).forEach((one) => {\r\n            let name = one.getAttribute('name')\r\n            let membershipDateName = name.replace('isUnlimited', 'membershipUntil')\r\n            let mu = table.querySelector('[name=\"'+membershipDateName+'\"]')\r\n            let muLabel = table.querySelector('[data-for=\"'+membershipDateName+'\"]')\r\n            if (one.checked) {\r\n                mu.classList.add('contentHidden')\r\n                muLabel.classList.add('contentHidden')\r\n            } else {\r\n                mu.classList.remove('contentHidden')\r\n                muLabel.classList.remove('contentHidden')\r\n            }\r\n\r\n        })\r\n    })\r\n}\r\n\r\ndocument.addEventListener('click', (event) => {\r\n    if (event.target.matches('.fapiMembership .isUnlimitedInput')) {\r\n        doHideMembershipUntil()\r\n    }\r\n})\r\n\r\ndocument.addEventListener('DOMContentLoaded', doHideMembershipUntil)\r\n\r\ndocument.addEventListener('click', (event) => {\r\n    if (event.target.matches('.levels .up') || event.target.matches('.levels .down')) {\r\n        let direction = (event.target.matches('.levels .up')) ? 'up' : 'down';\r\n        let id = event.target.parentNode.getAttribute('data-id');\r\n        console.log(id)\r\n        console.log(direction)\r\n        let form = document.getElementById('LevelOrderForm')\r\n        form.querySelector('[name=\"id\"]').setAttribute('value', id)\r\n        form.querySelector('[name=\"direction\"]').setAttribute('value', direction)\r\n        form.submit()\r\n    }\r\n})\n\n//# sourceURL=webpack://fapi-member/./media/fapi.js?");
/******/ })()
;