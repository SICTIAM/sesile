import React, {Component} from 'react'
import { array, string, bool, func } from 'prop-types'
import { Link } from 'react-router-dom'
import ClasseursButtonList from '../classeur/ClasseursButtonList'
import ClasseurProgress from '../classeur/ClasseurProgress'
import { translate } from 'react-i18next'

class Classeurs extends Component {

    constructor(props) {
        super(props)
    }

    static contextTypes = {
        t: func
    }

    static defaultProps = {
        displayButtons: true
    }

    render () {

        const { classeurs, title, displayButtons } = this.props
        const { t } = this.context

        return (
            <div className="grid-x grid-padding-x panel list-dashboard">
                <div className="cell medium-12 panel-heading">{ title }</div>
                <div className="cell medium-12 panel-body">
                    <div className="grid-x">
                        <div className="cell auto text-bold">{ t('common.classeurs.sort_label.name') }</div>
                        <div className="cell auto text-bold">{ t('common.classeurs.sort_label.limit_date') }</div>
                        {
                            displayButtons &&
                            <div className="cell auto text-bold">{ t('common.classeurs.sort_label.actions') }</div>
                        }

                    </div>
                </div>

                {
                    classeurs &&
                        classeurs.map((classeur) => (
                            <Link to={`/classeur/${classeur.id}`} className="cell medium-12 panel-body" key={classeur.id}>
                                <div className="grid-x align-middle">
                                    <div className="cell auto text-bold">
                                        { classeur.nom }
                                    </div>
                                    <div className="cell auto text-justify">
                                        <ClasseurProgress creation={classeur.creation} validation={classeur.validation} />
                                    </div>
                                    {
                                        displayButtons &&
                                        <div className="cell auto">
                                            <ClasseursButtonList classeur={classeur} />
                                        </div>
                                    }
                                </div>
                            </Link>
                        ))
                }

            </div>
        )
    }
}

Classeurs.propTypes = {
    classeurs: array.isRequired,
    title: string,
    displayButtons: bool
}

export default translate('sesile')(Classeurs)