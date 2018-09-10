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
        0: '#c82d2e',
        1: '#e2661d',
        2: '#2d6725',
        3: '#1c43a2',
        4: '#356bfc'
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
            <div
                id={classeur.id}
                className="grid-x panel-body align-middle text-center"
                style={{
                    minHeight: '3em',
                    borderLeft: `10px solid ${this.statusColorClass[classeur.status]}`,
                    fontSize: '.75em',
                    borderRadius: '5px 0px 0px 5px'}}>
                <Link
                    title={classeur.nom}
                    className="classeur-name cell small-9 medium-8 large-6 text-left text-bold text-wrap"
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
                    className="cell small-3 medium-2 large-1 align-center dropdown-valign"
                    data-toggle={"example-dropdown-" + classeur.id}
                    style={{display: 'flex'}}>
                    <ClasseurProgress
                        classeur={classeur}
                        creation={classeur.creation}
                        validation={classeur.validation} />
                </div>
                <div className="cell large-1 text-bold show-for-large">
                    {classeur.type.nom }
                </div>
                <CircuitListClasseur
                    classeurId={classeur.id}
                    etape_classeurs={classeur.etape_classeurs}
                    user={classeur.user} />
                <div className="cell large-2 show-for-large">
                    <ClasseursButtonList
                        classeurs={[classeur]}
                        validClasseur={validClasseur}
                        signClasseur={signClasseur}
                        revertClasseur={revertClasseur}
                        refuseClasseur={refuseClasseur}
                        removeClasseur={removeClasseur}
                        deleteClasseur={deleteClasseur}
                        id={classeur.id}
                        user={this.props.user}
                        check={this.props.checkClasseur}
                        checked={this.props.classeur.checked}
                        style={{fontSize: '0.8em'}}/>
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