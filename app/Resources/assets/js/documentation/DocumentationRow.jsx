import React, { Component } from 'react'
import { func, object } from 'prop-types'
import { translate } from 'react-i18next'
import { Link } from 'react-router-dom'
import Moment from 'moment'

class DocumentationRow extends Component {

    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props);
    }

    render() {

        const { documentation } = this.props
        const { t } = this.context

        Moment.locale(t('common.locale'))

        return (
            <div className="cell medium-12">
                <div className="grid-x grid-padding-x grid-padding-y">
                    <div className="medium-3 cell">{ documentation.description }</div>
                    <div className="medium-3 cell">{ documentation.version }</div>
                    <div className="medium-3 cell">{ Moment(documentation.date).format('LL') }</div>
                    <div className="medium-3 cell">
                        <Link to={ "/uploads/docs/" + documentation.path } className="button primary" target="_blank">{ t('common.help_board.view_button') }</Link>
                    </div>
                </div>
            </div>
        )
    }

}

DocumentationRow.PropTypes = {
    documentation: object.isRequired
}

export default translate(['sesile'])(DocumentationRow)