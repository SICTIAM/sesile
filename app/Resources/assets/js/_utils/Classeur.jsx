import React from 'react'
import { func } from 'prop-types'
import {handleErrors} from "./Utils";
import History from "./History";
import {basicNotification} from "../_components/Notifications";

const refusClasseur = (that, url, id, motif = '', type = '') => {
    fetch(Routing.generate(url, {id}),
        {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                motif: motif
            }),
            credentials: 'same-origin'
        })
        .then(handleErrors)
        .then(response => response.json())
        .then(classeur => {
            type === 'list'
                ? that.listClasseurs(that.state.sort, that.state.order, that.state.limit, that.state.start, that.state.userId)
                : that.setState({classeur})
        })
        .then(() => History.push(`/classeurs/valides`))
}


const actionClasseur = (that, url, id, method = 'PUT', type = '') => {
    fetch(Routing.generate(url, {id}),
        {
            method: method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(handleErrors)
        .then(response => response.json())
        .then(classeur => {
            type === 'list'
                ? that.listClasseurs(that.state.sort, that.state.order, that.state.limit, that.state.start, that.state.userId)
                : that.setState({classeur})
        })
        .then(() => {
            if (method === 'PUT') {
                that.context._addNotification(basicNotification(
                    'success',
                    that.context.t('classeur.success.edit')))
                History.push(`/classeurs/valides`)
            } else {
                that.context._addNotification(basicNotification(
                    'success',
                    that.context.t('classeur.success.delete')))
                History.push(`/classeurs/supprimes`)
            }
        })
        .catch(error => that.context._addNotification(basicNotification(
            'error',
            that.context.t('classeur.error.edit', {errorCode: error.status}),
            error.statusText)))
}

const Intervenants = ({classeur}) => {
    const currentFlowStep = classeur.etape_classeurs.find(etape_classeur => etape_classeur.etape_validante)
    return (
        classeur.status !== 2 &&
            <ul className="no-bullet">
                {currentFlowStep ?
                    currentFlowStep.users.map(user =>
                        <li key={`${user._nom}-${user.id}`}>
                            {user._prenom + " " + user._nom}
                        </li>)
                    .concat(currentFlowStep.user_packs.map(user_pack =>
                        <li key={`${user_pack._nom}-${user_pack.id}`}>
                            {user_pack.nom}
                        </li>)) :
                    `${classeur.user._prenom} ${classeur.user._nom}`}
            </ul>
    )
}

const StatusLabel = ({status}, {t}) => {
    const statusTranslation = Object.freeze({
        0: 'refused',
        1: 'pending',
        2: 'finished',
        3: 'withdrawn',
        4: 'retracted'
    })
    const statusColor = Object.freeze({
        0: '#c82d2e',
        1: '#f48c4f',
        2: '#39922c',
        3: '#2068a2',
        4: '#34a3fc'
    })
    return (
        <span
            className={`ui label labelStatus`}
            style={{
                color: '#fff',
                backgroundColor: statusColor[status],
                textAlign: 'center',
                width: '80px',
                padding: '5px',
                fontSize: '0.9em'}}>
            {t(`common.classeurs.status.${statusTranslation[status]}`)}
        </span>)
}

StatusLabel.contextTypes = {
    t: func
}

export { refusClasseur, actionClasseur, Intervenants, StatusLabel }