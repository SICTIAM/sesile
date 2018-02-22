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

    render () {

        const { classeurs, title } = this.props
        const { t } = this.context

        return (
            <div className="grid-x grid-padding-x panel">
                <div className="cell medium-12">
                    <div className="grid-x panel-heading align-middle ">
                        <div className="cell medium-12">{ title }</div>
                    </div>
                    <div className="grid-x grid-padding-x panel-body dashboard-title">
                        <div className="cell auto text-bold">{ t('common.classeurs.sort_label.name') }</div>
                        <div className="cell auto text-bold">{ t('common.classeurs.sort_label.limit_date') }</div>
                    </div>
                    {
                        classeurs &&
                        classeurs.map((classeur) => (
                            <Link to={`/classeur/${classeur.id}`} className="grid-x panel-body grid-padding-x align-middle dashboard-content" key={classeur.id}>
                                <div className="cell auto text-bold">
                                    { classeur.nom }
                                </div>
                                <div className="cell auto text-justify">
                                    <ClasseurProgress creation={classeur.creation} validation={classeur.validation} />
                                </div>
                            </Link>
                        ))
                    }
                </div>

            </div>
        )
    }
}

Classeurs.propTypes = {
    classeurs: array.isRequired,
    title: string
}

export default translate('sesile')(Classeurs)