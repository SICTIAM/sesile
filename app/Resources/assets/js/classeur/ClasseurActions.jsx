import React, { Component } from 'react'
import { func, array, number, string } from 'prop-types'
import {Button, Textarea} from '../_components/Form'
import { translate } from 'react-i18next'
import Moment from 'moment/moment'

class ClasseurActions extends Component {
    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props)
    }

    render() {

        const { actions, action, addComment, submitComment } = this.props
        const { t } = this.context

        return (
            <div className="grid-x panel grid-padding-y">
                <div className="cell medium-12" style={{paddingBottom: 0}}>
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <h3 className="cell medium-12">{ t('common.classeurs.comments.name') }</h3>
                    </div>

                    <div className="grid-x grid-margin-x grid-padding-x align-top">
                        <Textarea
                            id="new-action"
                            name="newAction"
                            placeholder={t('common.classeurs.comments.new')}
                            onChange={addComment}
                            className="cell medium-12 small-12"
                            value={action}
                            style={{width: '100%', height: '78px'}}
                        />
                    </div>

                    <div className="grid-x grid-margin-x grid-padding-x grid-margin-bottom align-top align-right">
                        <Button labelText={ t('common.classeurs.comments.submit') }
                                className="cell medium-10 text-right"
                                onClick={submitComment}/>
                    </div>
                    <div style={{height:"22em", overflow:"auto"}}>
                    { actions && actions.map((action) =>
                        <div key={action.id}>
                            <hr style={{height: '0.2rem', margin: '1rem auto'}}/>
                            <div className="align-middle" style={{display: 'flex'}}>
                                <div className="" style={{marginLeft: '0.5em', display: 'inline-block'}}>
                                    <i className="fa fa-comment" style={{fontSize: '1.2em'}} />
                                </div>
                                <div
                                    className="text-left"
                                    style={{
                                        display: 'inline-block',
                                        width: '90%',
                                        marginLeft: '1em'}}>
                                    {action.action &&
                                        <div>
                                            <span className="text-bold">{action.action}</span>
                                            <br />
                                        </div>
                                    }
                                    {action.commentaire &&
                                    <div>
                                        {action.commentaire}
                                        <br/>
                                    </div>
                                    }
                                    <span className="text-author text-capitalize">
                                        {action.user_action ?
                                            `${action.user_action._prenom}  ${action.user_action._nom}` :
                                            `${action.username}`}
                                    </span>
                                    <span className="text-date">
                                        {` ${t('common.classeurs.comments.the')} ${Moment(action.date).format('Do MMMM YYYY à HH:mm:ss')}`}
                                    </span>
                                </div>
                            </div>
                        </div>
                    )}
                    </div>
                </div>
            </div>

        )
    }
}

ClasseurActions.propsType = {
    actions: array,
    action: string,
    classeur: number,
    addComment: func,
    submitComment: func
}

export default translate(['sesile'])(ClasseurActions)