import React, { Component } from 'react'
import { func, array, number, string } from 'prop-types'
import { translate } from 'react-i18next'

import {basicNotification} from '../_components/Notifications'
import {handleErrors} from '../_utils/Utils'

class ClasseurPagination extends Component {
    static contextTypes = {
        t: func
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
    previousAndNextButtonStyle = {
        fontSize: '1.9em',
        background: 'white',
        cursor: 'pointer'
    }
    pageNumberButtonHoverStyle = {
        minWidth: '2.1em',
        textAlign: 'center',
        borderRadius: '50%',
        padding: '0.6em',
        cursor: 'pointer'
    }

    render() {
        const { t } = this.context
        const { limit, start, changePage, url } = this.props

        let pagesDisplay = []
        let pages = this.props.nbElementTotal / limit
        const buttonStyle = {fontSize: '0.775em'}
        const currentPage = start/limit
        if (currentPage === 0) {
            pagesDisplay.push(
                <li style={buttonStyle} key="previous" className="disabled">
                    <span style={this.previousAndNextButtonStyle} className="fa fa-chevron-circle-left"/>
                </li>)
        } else {
            pagesDisplay.push(
            <li style={buttonStyle} key="previous">
                <button
                    style={this.previousAndNextButtonStyle}
                    className="fa fa-chevron-circle-left primary"
                    onClick={() => this.props.changePreviousPage()}
                    aria-label={t('common.classeurs.pagination.previous')}/>
            </li>)
        }
        for (let page = 0; page < pages; page++ ) {
            (currentPage === page)
                ? pagesDisplay.push(<li key={page} className="current show-for-large">{page + 1}</li>)
                : pagesDisplay.push(
                    <li style={buttonStyle} key={page} className="show-for-large">
                        <button
                            style={this.pageNumberButtonHoverStyle}
                            onClick={() => changePage(page)}
                            aria-label={t('common.classeurs.pagination.page') + " " + page + 1}>
                            {page + 1}
                        </button>
                    </li>)
        }
        if (currentPage === Math.ceil(pages) -1) {
            pagesDisplay.push(
                <li style={buttonStyle} key="next" className="disabled">
                    <span style={this.previousAndNextButtonStyle} className="fa fa-chevron-circle-right"/>
                </li>)
        } else {
            pagesDisplay.push(
                <li style={buttonStyle} key="next">
                    <button
                        style={this.previousAndNextButtonStyle}
                        className="fa fa-chevron-circle-right primary"
                        onClick={() => this.props.changeNextPage()}
                        aria-label="common.classeurs.pagination.next"/>
                </li>)
        }

        return (
            <ul
                className="align-middle pagination float-right"
                role="navigation"
                aria-label="Pagination">
                {pagesDisplay.map(pageDisplay => pageDisplay)}
            </ul>
        )
    }
}

export default translate(['sesile'])(ClasseurPagination)