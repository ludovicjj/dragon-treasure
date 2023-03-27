import React from 'react';
import TreasureLogin from "../TreasureLogin";
import PropTypes from 'prop-types';

export default class TreasureConnectApp extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            user: this.props.user
        }
        this.onUserAuthenticated = this.onUserAuthenticated.bind(this);
    }

    async onUserAuthenticated(uri) {
        const response = await fetch(uri);
        const user = await response.json();
        this.setState({
            user: user
        })
    }

    render() {
        return <div className="card-wrapper">
            <TreasureLogin userAuthenticated={this.onUserAuthenticated}/>
            <div className="card card-info">
                <div className="card-body">
                    <div className="user text-center">
                        {this.state.user ? (
                            <div>
                                Authenticated as <strong>{this.state.user.username}</strong>
                                | <a href="/logout" className="underline">Log out</a>
                                <br/>
                                <h3 className="text-start mt-2">Tokens</h3>
                                {this.props.tokens ? (
                                    <div className="text-start">
                                        <ul className="list-group">
                                            {this.props.tokens.map(token => <li key={token} className="list-group-item">{token}</li>)}
                                        </ul>
                                    </div>
                                ) : (
                                    <div>Refresh to see tokens...</div>
                                )}
                            </div>
                            ) : (
                            <div>
                                Not Authenticated
                                <hr className="my-5 mx-auto separator"/>
                                <p>Check out the <a href="/api" className="underline">API Docs</a></p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    }
}
TreasureConnectApp.propTypes = {
    user: PropTypes.object,
    tokens: PropTypes.array
}