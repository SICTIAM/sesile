import React, { Component } from 'react'
import { func, array, number, string } from 'prop-types'
import { translate } from 'react-i18next'

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
        background: '#fefefe',
        cursor: 'pointer'
    }
    pageNumberButtonHoverStyle = {
        width: "25px",
        height: "25px",
        borderRadius: "15px",
        color: "white",
        background: "#34a3fc",
        textAlign: "center",
        margin: "5px"
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
                    <span key="disabled-pagination-previous" style={this.previousAndNextButtonStyle} className="fa fa-chevron-circle-left disabled"/>)
        } else {
            pagesDisplay.push(
                <span
                    key="enabled-pagination-previous"
                    style={this.previousAndNextButtonStyle}
                    className="fa fa-chevron-circle-left primary"
                    onClick={() => this.props.changePreviousPage()}
                    aria-label={t('common.classeurs.pagination.previous')}/>)
        }
        for (let page = 0; page < pages; page++ ) {
            (currentPage === page)&&
                pagesDisplay.push(
                    <div key={page} className="current" style={this.pageNumberButtonHoverStyle}>
                            {page + 1}
                    </div>)
        }
        if (currentPage === Math.ceil(pages) -1) {
            pagesDisplay.push(
                <span key="disabled-pagination-next" style={this.previousAndNextButtonStyle} className="fa fa-chevron-circle-right disabled"/>)
        } else {
            pagesDisplay.push(
                <span
                    key="enabled-pagination-next"
                    style={this.previousAndNextButtonStyle}
                    className="fa fa-chevron-circle-right primary"
                    onClick={() => this.props.changeNextPage()}
                    aria-label="common.classeurs.pagination.next"/>)
        }

        return (
            <div
                className="align-middle float-right"
                style={{width: "100%", display: "flex"}}
                role="navigation"
                aria-label="Pagination">
                {pagesDisplay.map(pageDisplay => pageDisplay)}
            </div>
        )
    }
}

export default translate(['sesile'])(ClasseurPagination)