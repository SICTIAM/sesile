import React, { Component } from 'react'
import { number, func } from 'prop-types'
import Classeurs from './Classeurs'
import { translate } from 'react-i18next'

class ClasseursList extends Component {

    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props);
    }

    render(){
        const { t } = this.context

        return (
            <div className="grid-x grid-margin-x grid-padding-x grid-padding-y align-center-middle">
                <div className="cell medium-12 text-center">
                    <h1>{t('common.menu.list_classeur')}</h1>
                </div>
                <div className="cell medium-12 head-list-classeurs">
                    <Classeurs url="sesile_classeur_classeurapi_list" userId={this.props.userId} user={this.props.user} />
                </div>
            </div>
        )
    }
}

ClasseursList.PropTypes = {
    userId: number
}

export default translate(['sesile'])(ClasseursList)