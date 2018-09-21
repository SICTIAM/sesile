import React, { Component } from 'react'
import { number, func } from 'prop-types'
import Classeurs from './Classeurs'
import { translate } from 'react-i18next'

class ClasseursRetract extends Component {

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
                    <h2>{t('common.menu.retractable_classeur')}</h2>
                </div>
                <Classeurs url="sesile_classeur_classeurapi_listretract"/>
            </div>
        )
    }
}

ClasseursRetract.PropTypes = {
    userId: number
}

export default translate(['sesile'])(ClasseursRetract)