import React, { Component } from 'react'
import { func, array, number, string } from 'prop-types'
import { translate } from 'react-i18next'
import {basicNotification} from '../_components/Notifications'
import {handleErrors} from '../_utils/Utils'
import {Select} from '../_components/Form'

class ClasseurPagination extends Component {
    static contextTypes = {
        t: func
    }

    state = {
        classeurs: []
    }

    static propTypes = {
        limit: number.isRequired,
        start: number.isRequired,
        changeLimit: func.isRequired,
        changePage: func.isRequired,
        changePreviousPage: func.isRequired,
        changeNextPage: func.isRequired,
        url: string
    }

    componentDidMount() {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_classeur_classeurapi_listall'), { credentials: 'same-origin' })
            .then(handleErrors)
            .then(response => response.json())
            .then(classeurs => this.setState({classeurs}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('common.classeurs.name'), errorCode: error.status}),
                error.statusText)))
    }

    render() {
        const { t } = this.context
        const { classeurs } = this.state
        const { limit, start, changeLimit, changePage, changePreviousPage, changeNextPage, url } = this.props

        const limits = [15,30,50,100]
        const listLimit = limits.map(limit => <option key={limit} value={limit}>{limit}</option>)

        let pagesDisplay = []
        let pages

        if (url === "sesile_classeur_classeurapi_list") {
            pages = (classeurs.length / limit)
        } else if (url === "sesile_classeur_classeurapi_valid") {
            pages = (classeurs.filter(classeur => classeur.validable).length / limit)
        } else if (url === "sesile_classeur_classeurapi_listretract") {
            pages = (classeurs.filter(classeur => classeur.retractable).length / limit)
        } else if (url === "sesile_classeur_classeurapi_listremovable") {
            pages = (classeurs.filter(classeur => classeur.deletable).length / limit)
        } else {
            pages = (classeurs.length / limit)
        }

        const currentPage = start/limit
        if (currentPage === 0) {
            pagesDisplay.push(<li key="previous" className="pagination-previous disabled">{ t('common.classeurs.pagination.previous')} <span className="show-for-sr">{ t('common.classeurs.pagination.page')}</span></li>)
        } else {
            pagesDisplay.push(<li key="previous" className="pagination-previous"><a href="#" onClick={changePreviousPage} aria-label={ t('common.classeurs.pagination.previous')}>{ t('common.classeurs.pagination.previous')} <span className="show-for-sr">{ t('common.classeurs.pagination.page')}</span></a></li>)
        }
        for (let page = 0; page < pages; page++ ) {
            (currentPage === page)
                ? pagesDisplay.push(<li key={page} className="current show-for-large">{page + 1}</li>)
                : pagesDisplay.push(<li key={page} className="show-for-large"><a href="#" onClick={() => changePage(page)} aria-label={ t('common.classeurs.pagination.page') + " " + page + 1}>{page + 1}</a></li>)
        }
        if (currentPage === Math.ceil(pages) -1) {
            pagesDisplay.push(<li key="next" className="pagination-next disabled">{ t('common.classeurs.pagination.next')} <span className="show-for-sr">{ t('common.classeurs.pagination.page')}</span></li>)
        } else {
            pagesDisplay.push(<li key="next" className="pagination-next"><a href="#" onClick={changeNextPage} aria-label="common.classeurs.pagination.next">{ t('common.classeurs.pagination.next')} <span className="show-for-sr">{ t('common.classeurs.pagination.page')}</span></a></li>)
        }

        return (
            <div className="grid-x align-top grid-padding-y">
                <div className="cell medium-2"></div>
                <ul className="cell medium-8 pagination text-center" role="navigation" aria-label="Pagination">
                    {
                        pagesDisplay.map(pageDisplay => pageDisplay)
                    }
                </ul>
                <Select id="limit"
                        value={limit}
                        className="cell medium-2"
                        onChange={changeLimit}
                        children={listLimit} />
            </div>
        )
    }
}

export default translate(['sesile'])(ClasseurPagination)