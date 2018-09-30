import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import Patchs from "./Patchs";
import VideoCont from './Video'
import Helps from "./Helps";

class HelpBoard extends Component {

    static contextTypes = {
        t: func
    }

    render() {
        const { t } = this.context

        return (
            <div>
                <div className="grid-x grid-margin-x grid-padding-x align-top align-center grid-padding-y">
                    <div className="cell medium-12 text-center">
                        <h2>{t('common.help_board.title')}</h2>
                    </div>
                </div>
                <div className="grid-x grid-margin-x grid-padding-x grid-padding-y">
                    <div className="cell medium-12">
                        <div className="grid-x grid-padding-x list-dashboard align-center">
                            <VideoCont />
                            <Helps />
                            <Patchs />
                        </div>
                    </div>
                </div>
            </div>
        )
    }

}

export default translate(['sesile'])(HelpBoard)