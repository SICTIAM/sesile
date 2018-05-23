import React, { Component } from 'react'
import { func, object, int } from 'prop-types'
import { Link } from 'react-router-dom'
import { translate } from 'react-i18next'

import CircuitListClasseur from '../circuit/CircuitListClasseur'
import ClasseursButtonList from './ClasseursButtonList'
import ClasseurProgress from './ClasseurProgress'

class ClasseursRow extends Component {
    static contextTypes = {
        t: func
    }
    static defaultProps = {
        classeur: {
            checked: false
        }
    }
    status = Object.freeze({
        0: 'refused',
        1: 'pending',
        2: 'finished',
        3: 'withdrawn',
        4: 'retracted'
    })
    statusColorClass = Object.freeze({
        0: 'alert',
        1: 'warning',
        2: 'success',
        3: 'secondary',
        4: 'primary'
    })
    render(){
        const {
            classeur,
            validClasseur,
            signClasseur,
            revertClasseur,
            refuseClasseur,
            removeClasseur,
            deleteClasseur } = this.props
        let validUsers
        const validEtape = classeur.etape_classeurs.find(etape_classeur => etape_classeur.etape_validante)
        validEtape ?
            validUsers = validEtape.users.map(user =>
                <span
                    key={`${user._nom}-${user.id}`}
                    style={{display: 'inline-block', width: '100%'}}>
                    {user._prenom + " " + user._nom}
                </span>
                ).concat(validEtape.user_packs.map(user_pack =>
                    <span
                        key={`${user_pack._nom}-${user_pack.id}`}
                        style={{display: 'inline-block', width: '100%'}}>
                        {user_pack.nom}
                    </span>)) :
            validUsers = `${classeur.user._prenom} ${classeur.user._nom}`

        return (
            <div id={classeur.id} className="grid-x panel-body align-middle text-center" style={{minHeight: '5em'}}>
                <div className="cell large-1 align-center show-for-large" style={{display: 'flex'}}>
                    <span
                        className={
                            `${this.statusColorClass[classeur.status]}
                            label
                            text-bold
                            text-uppercase`}
                        style={{fontSize: '.73em', width: '70px', margin: '5px'}}>
                        {this.context.t(`common.classeurs.status.${this.status[classeur.status]}`)}
                    </span>
                </div>
                <Link
                    title={classeur.nom}
                    className="classeur-name cell small-6 medium-6 large-2 text-left text-bold text-truncate"
                    style={{paddingLeft: '5px'}}
                    to={`/classeur/${classeur.id}`}>
                    {classeur.nom}
                </Link>
                <Link
                    className="cell medium-2 large-2 text-bold align-center show-for-large"
                    to={`/classeur/${classeur.id}`}>
                    {classeur.status !== 2 &&
                        validUsers}
                </Link>
                <div
                    className="cell small-6 medium-5 large-2 dropdown-valign"
                    data-toggle={"example-dropdown-" + classeur.id}>
                    <ClasseurProgress creation={classeur.creation} validation={classeur.validation} />
                </div>
                <div className="cell large-2 text-bold show-for-large">
                    {classeur.type.nom }
                </div>
                <CircuitListClasseur
                    classeurId={classeur.id}
                    etape_classeurs={classeur.etape_classeurs}
                    user={classeur.user} />
                <div className="cell large-2 show-for-large">
                    <ClasseursButtonList classeurs={[classeur]}
                                         validClasseur={validClasseur}
                                         signClasseur={signClasseur}
                                         revertClasseur={revertClasseur}
                                         refuseClasseur={refuseClasseur}
                                         removeClasseur={removeClasseur}
                                         deleteClasseur={deleteClasseur}
                                         id={"button-list-" + classeur.id}
                                         user={this.props.user}/>
                </div>
                <div className="cell medium-1 large-1 show-for-medium">
                    <input
                        type="checkbox"
                        id={classeur.id}
                        checked={this.props.classeur.checked}
                        onChange={event => this.props.checkClasseur(event)}
                        className="checkClasseur"/>
                </div>
            </div>

        )
    }
}

ClasseursRow.PropTypes = {
    classeur: object.isRequired,
    checkClasseur: func.isRequired,
    validClasseur: func,
    signClasseur: func,
    refuseClasseur: func,
    removeClasseur: func,
    deleteClasseur: func
}

export default translate(['sesile'])(ClasseursRow)